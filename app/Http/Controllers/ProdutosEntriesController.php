<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Products_entrie;
use App\Produtos;
use App\Supplier;
use Carbon\Carbon;
use App\Historical_alert;

class ProdutosEntriesController extends Controller
{
    //
    public function index(){
      if(Auth::check()){

        $product = Produtos::all();
        $products_entrie = Products_entrie::paginate(6);
        $alerts_count = Historical_alert::all()->count('id');

        return view('produtos_entries.index', array('products_entrie' => $products_entrie, 'products' => $product, 'buscar' => null, 'alerts_count' => $alerts_count));

      }else{
        return redirect('login');
      }
    }

    public function show($id){
      $produto = Products_entrie::find($id);
      return view('produtos_entries.show', array('produto'=> $produto));
    }

    public function create(){
      if(Auth::check()){
        $products = Produtos::all();
        $suppliers = Supplier::all();
        $alerts_count = Historical_alert::all()->count('id');

        return view('produtos_entries.create', compact('products', 'suppliers', 'alerts_count'));
      }else{
        return redirect('login');
      }
    }


    public function store(Request $request){

      $this->validate($request, [
        'produto'=>'required',
        'quantidade'=>'required|integer',
        'data_validade'=>'required|date|after:now',
        'fornecedor'=>'required',
      ]);

      $product = Produtos::find($request->input('produto'));
      $product->quantidade_total += $request->input('quantidade');
      $product->save();

      $produto = new Products_entrie();
      $produto->user_id = auth()->user()->id;
      $produto->produto_id = $request->input('produto');
      $produto->montante = $request->input('quantidade');
      //teste
      $produto->qtd_entrada = $request->input('quantidade');
      //
      $produto->data_validade = $request->input('data_validade');
      $produto->supplier_id = $request->input('fornecedor');

      if($produto->save()){
        return redirect('produtos_entries/')->with('alert-success', 'Entrada de Produto cadastrada com sucesso!!!');
      }
    }

    public function edit($id){
      if(Auth::check()){
        $products = Produtos::all();
        $produto_entries = Products_entrie::find($id);
        $suppliers = Supplier::all();
        $alerts_count = Historical_alert::all()->count('id');

      return view('produtos_entries.edit', compact('products', 'id', 'produto_entries', 'suppliers', 'alerts_count'));

      }else{
        return redirect('login');
      }
    }

    public function update(Request $request, $id){

      $this->validate($request, [
        'produto'=>'required',
        'quantidade'=>'required|integer',
        'data_validade'=>'required|date',
        'fornecedor'=>'required',
      ]);

      $products_entries = Products_entrie::find($id);
      $product = Produtos::find($products_entries->produto_id);

      //atualizando o estoque total
      if($request->get('quantidade') > $products_entries['montante']) {
          $new =  $request->get('quantidade') - $products_entries['montante'];
          $product['quantidade_total'] += $new;
      }
      else if($request->get('quantidade') < $products_entries['montante']) {
          $new = $products_entries['montante'] - $request->get('quantidade');
          $product['quantidade_total'] -= $new;
      }

      $products_entries->produto_id = $request->get('produto');
      $products_entries->montante = $request->get('quantidade');
      $products_entries->data_validade = $request->get('data_validade');
      $products_entries->supplier_id = $request->input('fornecedor');


      if($products_entries->save() and $product->save()){
        return redirect('produtos_entries/')->with('alert-success', 'Entrada de Produto atualizado com sucesso!!!');
      }
    }

    public function destroy($id){
      $products_entries = Products_entrie::find($id);


      $product = Produtos::find($products_entries['produto_id']);
      $product['quantidade_total'] -= $products_entries['montante'];
      $product->save();

      $products_entries->delete();

      return redirect('produtos_entries')->with('alert-success', 'Entrada de Produto deletada com sucesso!!!');

    }

    public function busca(Request $request){

      $alerts_count = Historical_alert::all()->count('id');
      $product = Produtos::all();

      $dataInicial = $request->input('dataInicial').' 00:00:00';
      $dataFinal   = $request->input('dataFinal').' 00:00:00';
      $produto     = $request->input('produto');

      $busca = Products_entrie::where( 'produto_id', '=',  $produto)
      ->whereBetween('created_at',[$dataInicial, $dataFinal])
      ->paginate(6);

      return view('produtos_entries.index', array('products_entrie'=> $busca, 'products'=> $product, 'alerts_count'=> $alerts_count));
    }

}
