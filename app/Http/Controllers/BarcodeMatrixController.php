<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ProductCategoriesController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Matrix\Exception;
use \Log;

use Yajra\DataTables\DataTables;
use \App\Classes\SystemID;
use \App\Classes\UserData;

use App\Models\Staff;
use App\Models\role;
use App\Models\usersrole;
use \App\Models\globaldata;
use App\Models\fakeModal;
use App\User;


class BarcodeMatrixController extends Controller
{
	public function matrix_next() {
		try {

			$user = new UserData();
			$asrar = DB::table("bmatrix")
				->insert([
					"subcategory_id" => 0,
					"merchant_id" => $user->company_id(),
					"created_at" => date('Y-m-d H:i:s'),
					"updated_at" => date('Y-m-d H:i:s')
				]);
			
			$msg = "New matrix added successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function main_table() {
		try {

			$user = new UserData();
			$merchant_id = $user->company_id();
			$data = DB::table('bmatrix')
				->where('merchant_id', $merchant_id )
				->whereNull('deleted_at')
				->orderBy('created_at','desc')
				->get();		

			$data->map(function($z) {

				if ($z->subcategory_id != 0) {

					$subcategory = DB::table('prd_subcategory')
						->where('id',$z->subcategory_id)
						->whereNull('deleted_at')
						->first();

					$cat = DB::table('prd_category')
						->where('id',$subcategory->category_id)
						->whereNull('deleted_at')
						->first();

				}

				if (request()->session()->has('category')) {
					$session_category	=	session()->get('category');

					foreach ($session_category as $value) {
						if (isset($value[$z->id])) {
							$cat = DB::table('prd_category')
								->where('id', $value[$z->id])
								->whereNull('deleted_at')
								->first();
							break;
						}
					}
				}


				$z->category = $cat ?? null;
				$z->subcategory = $subcategory->name ?? null;

				$z->color_count = DB::table('bmatrixcolor')
					->where('bmatrix_id',$z->id)
				 	->where([['color_id', '!=', 0]])
					->whereNull('deleted_at')
					->orderBy('created_at','desc')
					->get()->count();

				$ma_ids = DB::table('bmatrixattrib')->
					where('bmatrix_id', $z->id)->
					whereNull('deleted_at')->
					pluck('id');

				$atrib_item = DB::table('bmatrixattribitem')->
					whereNotNull('name')->
					whereNull('deleted_at')->
					whereIn('bmatrixattrib_id',$ma_ids)->
					pluck('bmatrixattrib_id')->
					toArray();

				$z->matrix_count = count(array_unique($atrib_item));

			});

			$data = collect($data);
			return Datatables::of($data)->

				addIndexColumn()->

				addColumn('cat', function ($data) {
					$cat = empty($data->category) ? "Category":$data->category->name;

					$html = <<<EOD
					<span class="os-linkcolor" onclick="show_catergory_modal($data->id)" style="cursor:pointer">$cat</span>
EOD;
					return $html;
				})->

				addColumn('subcat', function($data) {
					$cat = empty($data->category) ? 0:$data->category->id;
					$sub_cat = empty($data->subcategory) ? "Subcategory":$data->subcategory;
					$html = <<<EOD
						<span class="os-linkcolor sub_cat" style="cursor:pointer" onclick="show_subcategory_modal($cat,$data->id)">$sub_cat</span>
EOD;
					return $html;
				})->

				addColumn('colour', function($data) {
					$html = <<<EOD
					<span class="os-linkcolor" style="cursor:pointer" onclick="show_color_modal($data->id)">$data->color_count</span>
EOD;
					return $html;
				})->

				addColumn('matrix', function($data) {
					$url = route('setting.barcode.matrix',$data->id);
					return "<a href='$url' class='os-linkcolor' style='text-decoration:none;' target='_blank'><span>$data->matrix_count</span></a>";
				})->

				addColumn('del', function($data) {
					$img = asset('/images/redcrab_50x50.png');
					return "<img src='$img' onclick='delete_matrix($data->id)' style='cursor:pointer' height='25'/>";
				})->

				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function update_category(Request $request) {
		try {

			$cat_id = $request->cat_id;
			$m_id = $request->m_id;
			$entry = [$m_id => $cat_id];
			if (!$request->session()->has('category')) {
				$request->session()->put('category',);
			}
			session()->push('category',$entry);

			DB::table('bmatrix')
				->where('id',$m_id)
				->whereNull('deleted_at')
				->update(["subcategory_id" => 0]);

			return response()->json(["status"=> 'true']);
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function update_subcategory(Request $request) {
		try {
			$user = new UserData();
			$merchant_id = $user->company_id();

			$subcat_id = $request->subcat_id;
			$m_id = $request->m_id;

			$is_subcat_exist = DB::table('prd_subcategory')
				->where('id',$subcat_id)
				->whereNull('deleted_at')
				->first();

			if (empty($is_subcat_exist)) {
				throw new \Exception("Sub category not found");
			}

			$is_category_exist = DB::table('bmatrix')->
				where([
					"subcategory_id"	=>	$subcat_id,
					"merchant_id"		=>	$merchant_id
				])->
				whereNull('deleted_at')->
				first();

			if (!empty($is_category_exist)) {
			
				$msg = "Barcode matrix for subcategory already exists";
			
			} else {

				DB::table('bmatrix')->
					where('id',$m_id)->
					whereNull('deleted_at')->
					update([
						"subcategory_id"=>$subcat_id
					]);

				$msg = "Subcategory updated";
			}

			return view('layouts.dialog', compact('msg'))->render();

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}


	public function colour_table(Request $request) {
		try {
			$data = DB::table('bmatrixcolor')
				->where('bmatrix_id',$request->m_id)
				->whereNull('deleted_at')
				->orderBy('created_at','desc')
				->get();

			$data->map(function($z) {
				if ($z->color_id == 0) {

					$z->name = "name";	
					$z->color = "#000";

				} else {

					$color = DB::table('color')
						->where('id',$z->color_id)
						->whereNull('deleted_at')
						->first();
					$z->color = $color->hex_code;
					$z->name = $color->name;
				}

				$z->mt_id = request()->m_id;
			});

			return Datatables::of($data)->

				addIndexColumn()->

				addColumn('set', function ($data) {
					return 'set';
				})->

				addColumn('colour', function($data) {
					$mt =  request()->m_id;
					$html = <<< EOD
					<input type="color"  
						class="palette-color-picker-button colorInput" 
						mt-id="$mt"
						mc-id="$data->id" value="$data->color">
EOD;
					return $html;
				})->
				addColumn('name', function($data) {
					return $data->name ?? 'name';
				})->
				addColumn('hex', function($data) {
					return $data->color  ?? 'Code';
				})->
				addColumn('del', function($data) {
					$img = asset('/images/redcrab_50x50.png');
					$btn = $data->add_btn ?? '';	
					$html = <<<EOD
						$btn
						<img src="$img" height="25" onclick="delete_color($data->id)" style='cursor:pointer' />
EOD;
					return   $data->add_btn ?? $html;
				})->


				escapeColumns([])->
				make(true);


		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function colour_new(Request $request) {
		try {
			$m_id = $request->m_id;

			$id = DB::table('bmatrixcolor')
				->insertGetId([
					"bmatrix_id" => $m_id,
					"color_id" => 0,
					"created_at" => date('Y-m-d H:i:s'),
					"updated_at" => date('Y-m-d H:i:s')
				]);

			$msg = "New colour added successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function colour_del(Request $request) {
		try {

			$mc_id = $request->mc_id;

			if (empty($mc_id)) {
				throw new \Exception("No Matrix parent id");
			}	

			$mc = DB::table('bmatrixcolor')
				->where('id',$mc_id)
				->whereNull('deleted_at')
				->update([
					"deleted_at" => date('Y-m-d H:i:s')
				]);

//			return response()->json(["success"=>"true"]);
			$msg = "Colour deleted successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function colour_update(Request $request) {
		try {

			$mc_id = $request->mc_id;
			$color = $request->color;

			$mc = DB::table('bmatrixcolor')
				->where('id',$mc_id)
				->whereNull('deleted_at');

			$is_color_exist	=	$mc->first();

			if (empty($is_color_exist)) {
				throw new \Exception("Invalid record");
			}
			
			$m_id = $is_color_exist->bmatrix_id;

			if ($is_color_exist->color_id != 0) {
				
				$mc->update([
						"deleted_at"	=>	date('Y-m-d H:i:s')
					]);

				$mc_id = null;
			}

			if (empty($mc_id)) {
			
				$mc_id = DB::table('bmatrixcolor')
					->insertGetId([
						"bmatrix_id" => $m_id,
						"color_id" => 0,
						"created_at" => date('Y-m-d H:i:s'),
						"updated_at" => date('Y-m-d H:i:s')
					]);


				$mc = DB::table('bmatrixcolor')
					->where('id',$mc_id)
					->whereNull('deleted_at');
			}

			$color_id = DB::table('color')->insertGetId([
				"name" => "name",
				"description" => "using for matrix",
				"rgb_code" => "0",
				"hex_code" => $color,
				"created_at" => date('Y-m-d H:i:s'),
				"updated_at" => date('Y-m-d H:i:s')
			]);

			$mc->update(["color_id" => $color_id]);

			/*if ($is_color_exist->color_id == 0) {
			} else {
				DB::table('color')->
					where('id', $is_color_exist->color_id)->
					update(["hex_code" => $color]);
			}
			*/
			$msg = "Colour updated successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function matrix_delete(Request $request) {
		try{

			$m_id = $request->m_id;

			DB::table('bmatrix')->where('id', $m_id)
					   ->whereNull('deleted_at')
					   ->update([
						   "deleted_at" => date('Y-m-d H:i:s')
					   ]);

			//			return response()->json(["deleted" => true]);

			$msg = "Matrix deleted successfully";
			return view('layouts.dialog', compact('msg'))->render();

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}
	public function matrix_landing($id) {
		try {
			$bmatrix = DB::table('bmatrix')->where('id', $id)->
				whereNull('deleted_at')->
				first();

			if (empty($bmatrix)) {
				throw new \Exception("Invalid barcode matrix id");
			}

			if ($bmatrix->subcategory_id != 0) {
				$sub_category 	= 	DB::table('prd_subcategory')->
					where('id', $bmatrix->subcategory_id)->
					whereNull('deleted_at')
						->first();

				$category		=	DB::table('prd_category')->
					where('id', $sub_category->category_id)
							  ->whereNull('deleted_at')
							  ->first();
			}  else {

				$sub_category	=	null;
				$category		=	null;

			}

			return view('settings.matrix_landing',compact(['id','sub_category','category']));
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}
	public function attrib_new(Request $request) {
		try {
			$bm_id = $request->bm_id;
			DB::table('bmatrixattrib')
				->insert([
					"bmatrix_id"	=>	$bm_id,
					"created_at"	=>	date('Y-m-d H:i:s'),
					"updated_at"	=>	date('Y-m-d H:i:s')
				]);

			
			$msg = "New matrix added successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function matrix_attr_delete(Request $request) {
		try {
			$m_id	=	$request->m_id;
			DB::table('bmatrixattrib')->where('id',$m_id)->
				whereNull('deleted_at')->
				update([
					"deleted_at"	=>	date('Y-m-d H:i:s')
				]);
				
			$msg = "Matrix deleted successfully";
			return view('layouts.dialog', compact('msg'))->render();

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function matrix_table(Request $request) {
		try {

			$bm_id = $request->bm_id;

			$data = DB::table('bmatrixattrib')
				->where('bmatrix_id',$bm_id)
				->whereNull('deleted_at')
				->get();

			$data->map(function($z) {
				$z->count = DB::table('bmatrixattribitem')->
					where("bmatrixattrib_id", $z->id)
				  ->whereNull('deleted_at')
				  ->whereNotNull('name')
				  ->get()->count();
			});

			return Datatables::of($data)->
				addIndexColumn()->

				addColumn('set', function ($data) {
					$name = empty($data->name) ? 'Name':$data->name;
					$name_f = $data->name ?? '';
					return "<span onclick='change_name($data->id,\"$name_f\")'  class='os-linkcolor' style='cursor:pointer'>$name</span>";
				})->

				addColumn('attr', function($data) {
					return '<span style="cursor:pointer" onclick="show_attr('.$data->id.')">'.$data->count.'</span>';
				})->

				addColumn('del', function($data) {
					$img = asset('/images/redcrab_50x50.png');
					$html = <<<EOD
						<img src="$img" style="cursor:pointer" onclick="delete_matrix_attr($data->id)" height="25" />
EOD;
					return $html;
				})->


				escapeColumns([])->
				make(true);

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);	
		}
	}

	public function update_name(Request $request) {
		try {
			
			$m_id 	= 	$request->m_id;
			$name	=	$request->name;
			
			$is_old_record = DB::table('bmatrixattrib')
				->find($m_id);
			$is_unique = DB::table('bmatrixattrib')
				->where([
					"name"	=> $name,
					"bmatrix_id" => $is_old_record->bmatrix_id
				])->
				whereNull('deleted_at')->first();
			
			if (!empty($is_unique)) {
				$msg = "Matrix name already exist";
	
			} else {
				if ($is_old_record->name != '') {
		
					 DB::table('bmatrixattrib')->
						 where('id',$m_id)->update([
							'deleted_at'	=>	date('Y-m-d')
						]);

					 $attrib_items = DB::table('bmatrixattribitem')->
						 where('bmatrixattrib_id', $is_old_record->bmatrix_id)->
						 whereNull('deleted_at')->
						 get();

			 	 	$m_id = DB::table('bmatrixattrib')->
						 insertGetId([
							"bmatrix_id"	=>	$is_old_record->bmatrix_id,
							"created_at"	=>	date('Y-m-d H:i:s'),
							"updated_at"	=>	date('Y-m-d H:i:s')
						]);

					 $attrib_items->map(function($z) use ($m_id) {
					 	DB::table('bmatrixattribitem')->
							insert([
								'bmatrixattrib_id'	=>	$m_id,
								'name'				=>	$z->name,
								'updated_at'		=>	date('Y-m-d Y:i:s'),
								'created_at'		=>	$z->created_at
							]);
					 });
				}


				DB::table('bmatrixattrib')->where([
					"id"	=>	$m_id
				])->whereNull("deleted_at")
				 ->update([
					 "name"			=>		$name,
					 "updated_at"	=> 		date('Y-m-d')
				 ]);

				$msg = "Matrix updated successfully";
			}

			return view('layouts.dialog', compact('msg'))->render();

		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}		

	public function attribute_new(Request $request){
		try{
			$ma_id	=	$request->ma_id;
			$id 	=	 DB::table('bmatrixattribitem')->insertGetId([
				'bmatrixattrib_id'	=> 	$ma_id
			]);

			return response()->json(["insert"	=>	true, 'id'	=>	$id]);
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function attr_table() {
		try {
			$mb_id = request()->mb_id;
			$data = DB::table('bmatrixattribitem')->where("bmatrixattrib_id", $mb_id)
										 ->whereNull('deleted_at')
										 ->get();

			if ($data->isEmpty()) {
				$data = collect();
				$data->add(new fakeModal);
			}

			foreach($data as $d) {
				$id	= request()->mb_id;
				$img = asset('/images/greencrab_50x50.png');
				$d->add_btn = <<< EOD
					<img src="$img" 
						id="new_colour_btn" class="Sellermenu" height="25" 
						onclick="attribute_new()"
						style="cursor:pointer" ma-id="$id"/>
EOD;
				break;
			}

			return Datatables::of($data)->

				addIndexColumn()->

				addColumn('attr', function($data) {	
					$m_id = request()->mb_id;
					$html = <<<EOD
					<input type="text" placeholder="Attribute" 
						class="form-control attribute-input input-30" 
						style="display: inline-flex;" a-id="$data->id"
						value="$data->name" m-id="$m_id"
						id="selectsubcategory" name="attribute"/>	
EOD;
					return $html;
				})->

				addColumn('del', function($data) {
					$img = asset('/images/redcrab_50x50.png');
					$btn = $data->add_btn ?? '';
					$html = <<<EOD
					$btn
					<img src="$img" style="cursor:pointer;"
						onclick="delete_attribute($data->id)"	
						 height="25" />
EOD;
					return $data->add_btn ?? $html;
				})->


				escapeColumns([])->
				make(true);
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function attribute_value(Request $request) {
		try {

			$a_id = request()->a_id;

			$name = request()->name;
		
			if ($name == '') {
				return $this->attribute_delete();
			}
			
			if (!empty($a_id)) {
				
				$is_value_exist = DB::table('bmatrixattribitem')->
					where('id',$a_id)->
					whereNull('deleted_at')->
					first();

				if ($is_value_exist->name != '') {
					$is_value_exist = DB::table('bmatrixattribitem')->
						where('id',$a_id)->
						whereNull('deleted_at')->
						update([
							'deleted_at'	=>	date('Y-m-d H:i:s'),
							'updated_at'	=>	date('Y-m-d H:i:s')
						]);

					$a_id = null;
				}
			}

			if (empty($a_id)) {
				$a_id = json_decode(json_encode($this->attribute_new($request)),true);
				$a_id = $a_id['original']['id'];
			}

			$data = DB::table('bmatrixattribitem')->where("id", $a_id)
				 ->whereNull('deleted_at')
				 ->update([
					 "name"			=>		$name,
					 "updated_at"	=>		date('Y-m-d H:i:s')
				 ]);
			return response()->json(["Updated" => true]);
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function attribute_delete() {
		try {
			$a_id = request()->a_id;
			$data = DB::table('bmatrixattribitem')->where("id", $a_id)
				 ->whereNull('deleted_at')
				 ->update([
				 	"deleted_at"	=>	date('Y-m-d H:i:s')
				]);

			} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function delete_product_bar_code(Request $request) {
		try {
			DB::table('productbmatrixbarcode')->
				where('id', $request->pb_id)->
				update([ "deleted_at" => date('Y-m-d H:i:s')]);
			$msg = "Barcode deleted successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch(\Exception $e) {
			Log::info($e);
			abort($e);
		}
	}

	public function generate_bar_code(Request $request) {
		try {
			$bm_id = $request->bm_id;
			$changed = false;
			//bmatrixbarcode
			$bmatrix =	DB::table('bmatrix')->where('id', $bm_id)->
				whereNull('deleted_at')->
				first();

			if (empty($bmatrix)) {
				throw new \Exception("Invalid barcode matrix");
			}

			$attributes = DB::table('bmatrixattrib')->
				where('bmatrix_id', $bm_id)->
				whereNull('deleted_at')->
				whereNotNull('name')->
				get();

			$bmatrixcolor = DB::table('bmatrixcolor')->
				where('bmatrix_id', $bm_id)->
				where([['color_id', '!=', 0]])->
				whereNull('deleted_at')->
				get();

			$attrib_items = DB::table('bmatrixattribitem')->
				whereIn('bmatrixattrib_id', 
				$attributes->pluck('id'))->
				whereNull('deleted_at')->
				whereNotNull('name')->
				get();

			$color_items = DB::table('color')->
				whereIn('id', $bmatrixcolor->pluck('color_id'))->
			//	whereNotIn('hex_code', ["#000","#000000"])->
				whereNull('deleted_at')->pluck('id');
			$array = [];

			foreach($attributes as $a) {
				$array[] = 	DB::table('bmatrixattribitem')->
					where('bmatrixattrib_id',$a->id)->
					whereNull('deleted_at')->pluck('id');
			}
			$combined_attr = $this->combos($array);
			$z_array = [];

			if (count($color_items) > 0 ) {
				foreach($color_items as $c) {
					if (count($combined_attr) > 0) {
						foreach($combined_attr as $a) {
							$a['color']  = $c;
							$z_array[] = $a;
						}
					} else {
						$z_array = Array(['color'=>$c]);
					}
				}
			} else {
			
				foreach($combined_attr as $a) {
					$a['color'] = 0;
					$z_array[] = $a;
				}
			}


			$is_exist = DB::table('bmatrixbarcode')
				->where('bmatrix_id', $bmatrix->id)
				->whereNull('deleted_at')->get();

			if($is_exist->count() > 0 ) {

				DB::table('bmatrixbarcode')
					->where('bmatrix_id', $bmatrix->id)
					->delete();
			}

			foreach($z_array as $barcode) {
				$bar = [];
				foreach ($barcode as $key => $id) {
					if ($key === 'color') {
						if ($id != 0) {	
							$bmatrixcolor_id = DB::table('bmatrixcolor')->
								where('color_id',$id)->
								first()->id;

							$bar[] = ["color_id" => $bmatrixcolor_id];

						} else {
							$bar[] = ["color_id" => 0];
						}
					} else {
						$bmatrixattribitem = DB::table('bmatrixattribitem')->
							where('id',$id)->first();
						$bmatrixattrib = DB::table('bmatrixattrib')->
							where('id',$bmatrixattribitem->bmatrixattrib_id)->
							first();
						$bar[] = [$bmatrixattrib->id => $bmatrixattribitem->id];
					}
				}

		///		$bar[] = ["bmatrix_id" => $bmatrix->id];
				$string = json_encode($bar);

				DB::table('bmatrixbarcode')->insert([
					"bmatrix_id"	=>	$bmatrix->id,
					"pbarcode"		=>	$string,
					"created_at"	=> date('Y-m-d H:i:s'),
					"updated_at"	=>	date("Y-m-d H:i:s")
				]);
				
			}
			
			if ($is_exist->count() > 0) {
				$ischanged =	DB::table('bmatrixbarcode')->
									where('bmatrix_id',$bmatrix->id)->
									whereNotIn('pbarcode',$is_exist->pluck('pbarcode')->toArray())->
									whereNull('deleted_at')->
									get();
			
			if ($ischanged->count() > 0) {
				$this->remove_exisiting_switchs($bmatrix->id);
			}

			}
			$msg = "Definition updated successfully";
			return view('layouts.dialog', compact('msg'))->render();
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}

	public function generate_product_bar_code(Request $request) {
		try {
			$product_systemid = $request->p_id;
			$product = DB::table('product')
				->where('systemid', $product_systemid)
				->whereNull('deleted_at')
				->first();

			if (empty($product)) {
				throw new \Exception("Invalid product");
			}		

			$prdsubcategory_id = $product->prdsubcategory_id;
			$bmatrix	=	DB::table('bmatrix')->
				where('subcategory_id', $prdsubcategory_id)->
				whereNull('deleted_at')->first();
			if (empty($bmatrix)) {
				throw new \Exception("Matrix not definded for the category");
			}
			$bmatrixbarcode = DB::table('bmatrixbarcode')->
				where('bmatrix_id', $bmatrix->id)->
				whereNull('deleted_at')->
				orderBy('created_at', 'asc')->get();

			$is_pmb_exist =  DB::table('productbmatrixbarcode')->
				where('product_id', $product->id)->
				whereNull('deleted_at')->
				orderBy('created_at', 'asc')->get();

			$superSet = DB::table('productbmatrixbarcode')->where([
				"product_id"		=>		$product->id
			])->whereNull('deleted_at')->pluck('bmatrixbarcodejson');

			//if new attrib had been added
			//for updating it

			if (!$is_pmb_exist->isEmpty()) {

				$isNewAny = $this->compare_firstElement($bmatrixbarcode, $is_pmb_exist);
				
				foreach($isNewAny as $new) {
					$getSet = DB::table('bmatrixattrib')->where('id' , $new)->
						whereNull('deleted_at')->first();
					
					if (!empty($getSet)) {
						$getAttribItem =	DB::table('bmatrixattribitem')->
							where('bmatrixattrib_id',$getSet->id)->
							whereNull('deleted_at')->get();

						$update_ = false;

						foreach($getAttribItem as $a_i) {
							foreach($is_pmb_exist as $productBarcode) {
								$barcode = json_decode($productBarcode->bmatrixbarcodejson, true);
								$barcode[]	=	[$new  => $a_i->id];

								$newString = json_encode($barcode);
								
								$barcode_numsting = $this->getBarcodeNumString($barcode);

								if ($update_ == true) {
									DB::table('productbmatrixbarcode')->
										where('id',$productBarcode->id)->update([
											"bmatrixbarcodejson"	=> 	$newString,
											"bmatrixbarcode"		=>	$barcode_numsting,
											"updated_at"			=> 	date('Y-m-d H:i:s'),
										]);
								} else {
									DB::table('productbmatrixbarcode')->insert([
										"product_id" 			=> 	$product->id,
										"bmatrixbarcodejson"	=> 	$newString,
										"bmatrixbarcode"		=>	$barcode_numsting,
										"updated_at"			=>	date('Y-m-d H:i:s'),
										"created_at"			=> 	$productBarcode->created_at
									]);
								}
							}

						//	$update_ = false;
						}
					}
				}
			}



			foreach($bmatrixbarcode as $bmc) {
				$bcode_array = json_decode($bmc->pbarcode, true);
				$bcode_array[] = ["systemid"=> $product->systemid];	
				$bcode_array[] = ["bmatrix_id"=> $bmc->bmatrix_id];	

				if(!$superSet->isEmpty()) {

					$isExist = $this->compare_exisiting_barcodes($superSet, $bcode_array);
					if ($isExist) {
						continue;
					}

				}
				
				$barcode_numsting = $this->getBarcodeNumString($bcode_array);

				$id = DB::table('productbmatrixbarcode')->insertGetId([
					"product_id"				=>		$product->id,
					"bmatrixbarcodejson"		=>		json_encode($bcode_array),
					"bmatrixbarcode"			=>		$barcode_numsting,
					"created_at"				=>		date('Y-m-d H:i:s'),
					'updated_at'				=>		date('Y-m-d H:i:s')
				]);
				

			}

			$is_changed  = $this->add_disable_switchs($bmatrix->id,$product->id);
			app('App\Http\Controllers\InventoryController')->update_barcode_oceania($product->id);
			if ($is_changed) {
				$msg = "Barcode updated";
				return view('layouts.dialog', compact('msg'))->render();
			}
		} catch (\Exception $e) {
			Log::info($e);
			abort(404);
		}
	}
	
	private function getBarcodeNumString($bar_code) {
		$string = '';
	
		foreach($bar_code as $val) {
			foreach($val as $key	=>	$val) {
				if ($key == 'systemid') {
					$string = $val.$string;	
				} else if ($key == 'bmatrix_id') {
					$string .= $val;
				} else if ($key == 'color_id') {
					$string .= $val;
				}  else {
					$string .= $key.$val;
				}

			}
		}
	
		return $string;
	}

	private function remove_exisiting_switchs($bmatrix_id) {
		try {
			DB::table('prdbmatrixbarcodegen')->
				where('bmatrix_id',$bmatrix_id)->
				delete();

			return true;
		} catch (\Exception $e) {
			
			Log::info([
				"Line No" 	=> 	$e->getLine(),
				"File"		=>	$e->getFile(),
				"Error"		=>	$e->getMessage()
			]);

			abort(404);
		}
	}
	private function add_disable_switchs($bmatrix_id,$product_id) {
		try {
			
			$is_exist  = !empty(
				DB::table('prdbmatrixbarcodegen')->
					where([
						"product_id"			=>	$product_id,
						"bmatrix_id"			=>	$bmatrix_id,
						"bmbarcode_generated"	=>	1,
					])->
					whereNull('deleted_at')->
					first()
				);
			
			if (!$is_exist) {

				DB::table('prdbmatrixbarcodegen')->
					insert([
						"product_id"			=>	$product_id,
						"bmatrix_id"			=>	$bmatrix_id,
						"bmbarcode_generated"	=>	1,
						"created_at"			=>	date('Y-m-d H:i:s'),
						"updated_at"			=>	date('Y-m-d H:i:s')
					]);	
				return true;
			}

			return false;
		} catch (\Exception $e) {
			
			Log::info([
				"Line No" 	=> 	$e->getLine(),
				"File"		=>	$e->getFile(),
				"Error"		=>	$e->getMessage()
			]);

			abort(404);
		}
	}

	private function combos($data, &$all = array(), $group = array(), $val = null, $i = 0) {
		if (isset($val)) {
			array_push($group, $val);
		}
		if ($i >= count($data)) {
			array_push($all, $group);
		} else {

			foreach ($data[$i] as $v) {
				$this->combos($data, $all, $group, $v, $i + 1);
			}
		}
		return $all;
	}

	//Compering SuperSet with the subSet\
	//while ignoring the extra fields in subSet in the intersection
	// Subset - Superset >= 0 {along values}
	private function compare_exisiting_barcodes($isExist, $subSet) {
		$is_equal = false;

		foreach($isExist as $record) {
			$compare = json_decode($record, true);
			$keys = [];

			foreach($compare as $value) {

				if (in_Array($value, $subSet)) {
					$is_equal = true;
				} else {
					$is_equal = false;
					break;
				}
			}

			if ($is_equal == true) {
				return true;
			}
		}

		return $is_equal;
	}

	private function compare_firstElement($superSet, $subSet) {

		$set_1_array = json_decode($superSet->last()->pbarcode,true);
		$set_2_array =	json_decode($subSet->last()->bmatrixbarcodejson, true);
	
		$set_key_1 = [];
		foreach ($set_1_array as $key => $val) {
			$set_key_1[] = array_keys($val)[0];
		}

		$set_key_2 = [];
		foreach($set_2_array as $val) {
			$set_key_2[] = array_keys($val)[0];
		}

		$result = [];

		foreach ($set_key_1 as $k) {
			if (!in_array($k, $set_key_2)) {
				$result[] = $k;
			}
		}

		return $result;
	}
}
