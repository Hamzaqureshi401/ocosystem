<?php

namespace App\Http\Controllers;

use App\Models\prd_subcategory;
use Log;
use App\Models\SettingBarcodeMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \App\Classes\UserData;
use \App\Http\Controllers\CountryController;
use \App\Http\Controllers\FinancialYearController;
use \App\Http\Controllers\OrganisationHierarchyController as OH;
use \App\Models\attachment;
use \App\Models\Company;
use \App\Models\companycontact;
use \App\Models\contact;
use \App\Models\director;
use \App\Models\terminal;
use \App\Models\location;
use \App\Models\locationterminal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Psy\Util\Json;

class SettingController extends Controller
{
    //

    protected $user_data;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('CheckRole:stg');

    }

    public function general()
    {
        $this->user_data = new UserData();
        $company_id = $this->user_data->company_id();

        $FYData = new FinancialYearController();
        $FYData = $FYData->showFinancialYearView();

        $country_data = new CountryController();
        $country_data = $country_data->showCountryView();

        $OH = new OH();

        $department = $OH->fetchData('department');
        $position = $OH->fetchData('position');

        $country_list = $country_data['country'];
        $currency_list = $country_data['currency'];
        $selected_country = $country_data['selected_country'];
        $selected_currency = $country_data['selected_currency'];

        $this_company = Company::findOrFail($company_id);
        $director = director::where('company_id', $company_id)->get();
        $contacts = contact::whereIn('id', companycontact::where('company_id', $company_id)->pluck('id'))->get();
        $attachment = attachment::where('company_id', $company_id)->get();

        $productsetting = new ProductCategoriesController();
        $product_category = $productsetting->fetchData('category');
        $product_brand = $productsetting->fetchData('brand');

        $showMain = true;
        $showForm = true;

        return view('settings.general', compact([
            'FYData', 'country_list', 'currency_list', 'selected_country', 'selected_currency', 'department', 'position', 'showMain', 'showForm', 'this_company', 'director', 'contacts', 'attachment', "product_category", 'product_brand',
        ]));
    }

	public function loyalty_programme() {

		$user_data = new UserData();
		$company_data = DB::table('company')->where('id', $user_data->company_id())->first();
		$currency = DB::table('currency')->where('id', $company_data->currency_id)->first()->code ?? "MYR";
		return view('settings.loyalty_programme', compact('company_data', 'currency'));
	}


	public function loyalty_programme_update(Request $request) {

		Log::debug('loyalty_programme_update: all()='.
			json_encode($request->all()));

		$user_data = new UserData();

		DB::table('company')->
			where('id', $user_data->company_id())->
			update([
				'loyalty_pgm' => $request->r_val,
				'updated_at' => now()
			]);

		$franchiseSync = new FranchiseeOceaniaSync();
		$franchiseSync->syncCompanyToData($request->r_val);
		$msg = "Loyalty Programme updated";
		return view('layouts.dialog', compact('msg'));
	}


    public function barcode()
    {
        $productsetting = new ProductCategoriesController();
        $product_category = $productsetting->fetchData('category');
        return view('settings.barcode', compact('product_category'));
    }


    public function savebarcode(Request $request)
    {


        $update_subcat = prd_subcategory::where('category_id', $request->category)->where('id', $request->subcategory)->first();
        $update_subcat->is_matrix = 1;
        $update_subcat->save();
        $color = 0;
        $barcode_array = [];

        switch ($request->color_code) {
            case '#E91E63':
                $color = 1;
                break;
            case '#C2185B':
                $color = 2;
                break;
            case '#F8BBD0':
                $color = 3;
                break;
            case '#CDDC39':
                $color = 4;
                break;
            case '#212121':
                $color = 5;
                break;
            case '#727272':
                $color = 6;
                break;
            case '#B6B6B6':
                $color = 7;
                break;
            case '#000000':
                $color = 8;
                break;
            case '#111111':
                $color = 9;
                break;
            case '#121212':
                $color = 10;
                break;
        }

        if ($color) {
            for ($i = 0; $i < $request->sizes; $i++) {
                $barcodes = new SettingBarcodeMatrix();

                switch ($i) {
                    case 0:
                        $barcodes->size = "XS";
                        break;
                    case 1:
                        $barcodes->size = "S";
                        break;
                    case 2:
                        $barcodes->size = "M";
                        break;
                    case 3:
                        $barcodes->size = "L";
                        break;
                    case 4:
                        $barcodes->size = "XL";
                        break;
                    case 5:
                        $barcodes->size = "XXL";
                        break;

                }

                $barcodes->category_id = $request->category;
                $barcodes->subcategory_id = $request->subcategory;
                $barcodes->color_code = $request->color_code;
                $barcodes->color_id = $color;
                $barcodes->barcode_numbers = $i + 1 . "-" . $color;
                $barcodes->save();
            }
        } else {
            for ($i = 0; $i < $request->sizes; $i++) {

                $barcodes = new SettingBarcodeMatrix();

                switch ($i) {
                    case 0:
                        $barcodes->size = "XS";
                        break;
                    case 1:
                        $barcodes->size = "S";
                        break;
                    case 2:
                        $barcodes->size = "M";
                        break;
                    case 3:
                        $barcodes->size = "L";
                        break;
                    case 4:
                        $barcodes->size = "XL";
                        break;
                    case 5:
                        $barcodes->size = "XXL";
                        break;

                }
                $barcodes->category_id = $request->category;
                $barcodes->subcategory_id = $request->subcategory;
                $barcodes->barcode_numbers = $i + 1;
                $barcodes->save();
            }
        }


        return 1;

    }


    public function companyEditForm()
    {
        $this->user_data = new UserData();
        $company_id = $this->user_data->company_id();

        $this_company = Company::findOrFail($company_id);
        $director = director::where('company_id', $company_id)->get();
        $contacts = contact::whereIn('id', companycontact::where('company_id', $company_id)->pluck('id'))->get();
        $attachment = attachment::where('company_id', $company_id)->get();
        $editForm = true;
        return view('settings.generalinfo', compact(['editForm', 'this_company', 'director', 'contacts', 'attachment']));

    }

    public function companySaveForm(Request $request)
    {
        try {
            $this->user_data = new UserData();
            $validation = Validator::make($request->all(), [
                'company_name' => "required",
                'reg_no' => "required",
                // 'director' => "required",
                // 'nric' => "required",
                'address' => "required",
                // 'contact_name' => "required",
                // 'contact_mobile' => "required",
            ]);

            $company_id = $this->user_data->company_id();

            $this_company = Company::findOrFail($company_id);
            $this_company->name = $request->company_name;
            $this_company->business_reg_no = $request->reg_no;
            $this_company->gst_vat_sst = $request->gst_vat_sst;
            $this_company->office_address = $request->address;
            $this_company->save();

            director::where('company_id', $company_id)->delete();
            foreach ($request->director as $key => $value) {

                if ($request->nric[$key] == null || $request->director[$key] == null) {
                    continue;
                }
                $director = new director();
                $director->company_id = $company_id;
                $director->name = $request->director[$key];
                $director->nric = $request->nric[$key];
                $director->save();
            }

            contact::whereIn('id', companycontact::where('company_id', $company_id)->pluck('id'))->delete();
            companycontact::where('company_id', $company_id)->delete();

            foreach ($request->contact_name as $key => $value) {

                if ($request->contact_name[$key] == null || $request->contact_mobile[$key] == null) {
                    continue;
                }

                $contact = new contact();
                $companycontact = new companycontact();
                $contact->name = $request->contact_name[$key];
                $contact->mobile_no = $request->contact_mobile[$key];
                $contact->save();
                $companycontact->company_id = $company_id;
                $companycontact->contact_id = $contact->id;
                $companycontact->save();
            }

            $this_company = Company::findOrFail($company_id);
            $director = director::where('company_id', $company_id)->get();
            $contacts = contact::whereIn('id', companycontact::where('company_id', $company_id)->pluck('id'))->get();
            $attachment = attachment::where('company_id', $company_id)->get();

            // $showForm = true;
            // return view('settings.generalinfo',compact(['showForm','this_company','director','contacts','attachment']));
            return response()->json(["status" => "success"]);
        } catch (\Exception $e) {
            return response()->json(["status" => "failed", "Detail" => $e]);
        }

    }

    public function companySaveLogo(Request $request)
    {
        //  dd($request);
        $this->user_data = new UserData();

        if ($request->hasfile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension(); // getting image extension

            if (!in_array($extension, array(
                'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF'))) {
                return abort(403);
            }

            $filename = time() . '.' . $extension;
            $company_id = $this->user_data->company_id();
            $this->check_location("/images/company/$company_id/corporate_logo/");
            $file->move(public_path() . ("/images/company/$company_id/corporate_logo/"), $filename);

            $this_company = Company::findOrFail($company_id);

            $this_company->corporate_logo = $filename;
            $this_company->save();

            $return_arr = array("name" => $filename, "size" => 000, "src" => "/logo/$company_id/$filename");
            return response()->json($return_arr);
        } else {
            return abort(403);
        }
    }

    public function showLogo($company_id, $filename)
    {

        $is_logo = Company::where(['id' => $company_id, "corporate_logo" => $filename])->first();

        if ($is_logo) {

            $headers = array('Content-Type: application/octet-stream', "Content-Disposition: attachment; filename=$filename");

            $ext = pathinfo(public_path() . ("/images/company/$company_id/corporate_logo/$filename"), PATHINFO_EXTENSION);

            if ($ext == 'png' || 'PNG') {
                $headers = array('Content-Type' => 'image/png');
            } else if ($ext == 'jpg' || 'jpeg' || 'JPEG' || 'JPG') {
                $headers = array('Content-Type' => 'image/jpeg');
            }
            if (!file_exists(public_path() . "/images/company/$company_id/corporate_logo/$filename")) {
                return abort(500);
            }

            //now create your new response here
            $response = \Response::file(public_path() . ("/images/company/$company_id/corporate_logo/$filename"), $headers);

            //HERE IS THE MAGIC FOLKS
            ob_end_clean();

            //now this works like a charm; or whatever you like to call it;
            return $response;
        } else {
            return abort(403);
        }

    }

    public function companySaveNRIC(Request $request)
    {

        if ($request->hasfile('file')) {
            $this->user_data = new UserData();
            $file = $request->file('file');
            $blockNo = $request->blockNo;

            // if(strpos($blockNo, 'new') == false){
            //         attachment::findOrFail($blockNo)->delete();
            // }

            $extension = $file->getClientOriginalExtension(); // getting image extension
            $filename = time() . '.' . $extension;

            $company_id = $this->user_data->company_id();
            $attachment = new attachment();
            $attachment->company_id = $company_id;
            $attachment->name = $file->getClientOriginalName();
            $attachment->filename = $filename;
            $attachment->save();
            $location = "/images/company/$company_id/attachment/$attachment->id/";

            $this->check_location($location);

            $file->move(public_path() . ($location), $filename);
            $return_arr = array(
                "name" => $file->getClientOriginalName(),
                "size" => 000,
                "type" => in_array($extension, array(
                    'jpg', 'JPG', 'png', 'PNG', 'jpeg', 'JPEG', 'gif', 'GIF', 'bmp', 'BMP', 'tiff', 'TIFF',
                )) ? "image" : "doc",
                "src" => "/company/$company_id/attachment/$attachment->id/$filename",
                "id" => $attachment->id);

            return response()->json($return_arr);

        } else {
            return abort(403);
        }
    }

    public function showNRIC($company_id, $attachment_id, $filename)
    {
        $this->user_data = new UserData();
        $is_exist = attachment::where(["id" => $attachment_id, "company_id" => $company_id, "filename" => $filename])->first();
        if ($is_exist) {
            $headers = array('Content-Type: application/octet-stream', "Content-Disposition: attachment; filename=$filename");

            $location = "/images/company/$company_id/attachment/$attachment_id/$filename";

            if (!file_exists(public_path() . $location)) {
                return abort(500);
            }

            $response = \Response::file(public_path() . ($location), $headers);

            ob_end_clean();

            return $response;

        } else {
            return abort(500);
        }
    }

    public function delLogo()
    {
        $this->user_data = new UserData();
        $company_id = $this->user_data->company_id();
        $this_company = Company::findOrFail($company_id);
        $this_company->corporate_logo = null;
        $this_company->save();
        return response()->json(array("status" => "deleted"));
    }

    public function delNRIC(Request $request)
    {

        $this->user_data = new UserData();

        $validation = Validator::make($request->all(), [
            'fileName' => "required",
        ]);
        $fileName = $request->fileName;
        $company_id = $this->user_data->company_id();
        $is_exist = attachment::where(["filename" => $fileName, "company_id" => $company_id])->first();

        if ($is_exist) {
            $is_exist->delete();
            $count = attachment::where(["company_id" => $company_id])->get()->count();
            return response()->json(array("status" => "deleted", "count" => $count));
        } else {
            return response()->json(array("status" => "Error occured"));
        }
    }

    public function check_location($location)
    {
        $location = array_filter(explode('/', $location));
        $path = public_path();

        foreach ($location as $key) {
            $path .= "/$key";
            if (is_dir($path) != true) {
				Log::info('check_location: path='.$path);
                mkdir($path, 0777, true);
            }
        }
    }

    public function receipt_address(Request $request)
    {
        $terminal_id = (\Session::get('terminalID'));
        $terminal = strval($terminal_id);
        $this->user_data = new UserData();
        $company_id = $this->user_data->company_id();

        $address_enum = $request->get('address_enum');

        $address_line1 = $request->get('address_line1');
        $address_line2 = $request->get('address_line2');
        $address_line3 = $request->get('address_line3');

        $sstnumber = $request->get('sstnumber');
        $data = $request->all();
        //echo $terminal;
        if ($address_enum == 'branch') {
            terminal::where(['systemid' => $terminal])->update(['address_preference' => "branch"]);
            $terminal_id = terminal::where(['systemid' => $terminal])->pluck('id')->first();
            $location_id = locationterminal::where(['terminal_id' => $terminal_id])->pluck('location_id')->first();
            location::where(['id' => $location_id])->update(['address_line1' => $address_line1, 'address_line2' => $address_line2, 'address_line3' => $address_line3]);
        } else
            if ($address_enum == 'company') {
                terminal::where(['systemid' => $terminal])->update(['address_preference' => "company"]);
            }
        //echo $sstnumber;
        if ($sstnumber == 1) {
            terminal::where(['systemid' => $terminal])->update(['show_sst_no' => 1]);
        } else {
            terminal::where(['systemid' => $terminal])->update(['show_sst_no' => 0]);
        }
    }
    public function receipt_type(Request $request)
    {
        try{
            $terminal_id = (\Session::get('terminalID'));
            $terminal = strval($terminal_id);
            $this->user_data = new UserData();
            $company_id = $this->user_data->company_id();

            $receipt_type = $request->get('receipt_type');

            if ($receipt_type == 'serpae') {
                terminal::where(['systemid' => $terminal])->
					update(['receipt_type' => "serpae"]);
                $msg = "Receipt type changed to Serpae Tetra";

            } elseif ($receipt_type == 'neon') {
				terminal::where(['systemid' => $terminal])->
					update(['receipt_type' => "neon"]);
				$msg = "Receipt type changed to Neon Tetra";

            } elseif ($receipt_type == 'neon_escpos') {
                terminal::where(['systemid' => $terminal])->
					update(['receipt_type' => "neon_escpos"]);
                $msg = "Receipt type changed to Neon Tetra ESC/POS";

            } elseif ($receipt_type == 'pump_escpos') {
                terminal::where(['systemid' => $terminal])->
					update(['receipt_type' => "pump_escpos"]);
                $msg = "Receipt type changed to Pump Tetra ESC/POS";

            } else {
                if ($receipt_type == 'serpae_escpos') {
                    terminal::where(['systemid' => $terminal])->
						update(['receipt_type' => "serpae_escpos"]);
                    $msg = "Receipt type changed to Serpae Tetra ESC/POS";
                }
            }

            $data = view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = "Error occured while saving stock";

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }


    public function kitchen_type(Request $request)
    {
        try{
            $terminal_id = (\Session::get('terminalID'));
            $terminal = strval($terminal_id);
            $this->user_data = new UserData();
            $company_id = $this->user_data->company_id();

            $kitchen_type = $request->get('kitchen_type');
            log::debug('kitchen_type'.$kitchen_type);
            if ($kitchen_type == 'serpae_tetra') {
                terminal::where(['systemid' => $terminal])->
					update(['kitchen_type' => "serpae"]);
                $msg = "Kitchen chit changed to Serpae Tetra";

            } elseif ($kitchen_type == 'neon_tetra') {
                terminal::where(['systemid' => $terminal])->
					update(['kitchen_type' => "neon"]);
                $msg = "Kitchen chit changed to Neon Tetra";

            } else {
                if ($kitchen_type == 'serpae_escpos1') {
                    terminal::where(['systemid' => $terminal])->
						update(['kitchen_type' => "serpae_escpos"]);
                    $msg = "Kitchen chit changed to Serpae Tetra ESC/POS";
                }
            }

            $data = view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = "Error occured while saving stock";

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }


    public function print_mode(Request $request)
    {
        try{
            $terminal_id = (\Session::get('terminalID'));
            $terminal = strval($terminal_id);
            $this->user_data = new UserData();
            $company_id = $this->user_data->company_id();

            $print_mode = $request->get('print_mode');

            if ($print_mode == 'fast') {
                terminal::where(['systemid' => $terminal])->
					update(['print_mode' => "fast"]);
                $msg = "Print mode changed to fast print.";

            } else {   
                if ($print_mode == 'normal') {
                    terminal::where(['systemid' => $terminal])->
						update(['print_mode' => "normal"]);
                    $msg = "Print mode changed to normal print.";
                }
            }

            $data = view('layouts.dialog', compact('msg'));

        } catch (\Exception $e) {
            $msg = "Error occured while saving print mode";

            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() .
                ":" . $e->getMessage()
            );

            $data = view('layouts.dialog', compact('msg'));
        }
        return $data;
    }

	public function updateHeaderFooter(Request $request) {
		try {
			
			$validation = Validator::make($request->all(),[
				"field"	=>	"required",
	//			"data"	=>	"required"
			]);	

			if ($validation->fails())
				throw new \Exception("Validation fails");

			$user_data = new UserData();

			$is_exist = DB::table('merchantglobal')->
				where('merchant_id', $user_data->company_id())->
				first();

			$db_data = [];

			switch($request->field) {
				case 'Sales_Order':
					$db_data['so_footer'] = $request->data;
				break;
				case 'Sales_Order_logo':
					$db_data['so_has_logo'] = !empty($is_exist) ? !$is_exist->so_has_logo:true;
				break;
			
				case 'Purchase_Order':
					$db_data['po_footer'] = $request->data;
				break;
				case 'Purchase_Order_logo':
					$db_data['po_has_logo'] = !empty($is_exist) ? !$is_exist->po_has_logo:true;
				break;
			
				case 'Invoice':
					$db_data['inv_footer'] = $request->data;
				break;
				case 'Invoice_logo':
					$db_data['inv_has_logo'] = !empty($is_exist) ? !$is_exist->inv_has_logo:true;
				break;
			
				case 'Debit_Note':
					$db_data['dn_footer'] = $request->data;
				break;
				case 'Debit_Note_logo':
					$db_data['dn_has_logo'] = !empty($is_exist) ? !$is_exist->dn_has_logo:true;
				break;
			
				case 'Credit_Note':
					$db_data['cn_footer'] = $request->data;
				break;
				case 'Credit_Note_logo':
					$db_data['cn_has_logo'] = !empty($is_exist) ? !$is_exist->cn_has_logo:true;
				break;
		
				case 'Consignment_Note':
					$db_data['consign_footer'] = $request->data;
				break;
				case 'Consignment_Note_logo':
					$db_data['consign_has_logo'] = !empty($is_exist) ? !$is_exist->consign_has_logo:true;
				break;


				case 'Quotation':
					$db_data['quo_footer'] = $request->data;
				break;
				case 'Quotation_logo':
					$db_data['quo_has_logo'] = !empty($is_exist) ? !$is_exist->consign_has_logo:true;
				break;

				case 'Delivery_Order':
					$db_data['do_footer'] = $request->data;
				break;
				case 'Delivery_Order_logo':
					$db_data['do_has_logo'] = !empty($is_exist) ? !$is_exist->do_has_logo:true;
				break;

				case 'Receipt':
					$db_data['rcp_footer'] = $request->data;
				break;
				case 'Receipt_logo':
					$db_data['rcp_has_logo'] = !empty($is_exist) ? !$is_exist->rcp_has_logo:true;
				break;
				break;
			}

			if (empty($is_exist)) {
				$db_data['merchant_id'] = $user_data->company_id();
				$db_data['updated_at'] 	= date("Y-m-d H:i:s"); 
				$db_data['created_at'] 	= date("Y-m-d H:i:s"); 
				DB::table('merchantglobal')->
					insert($db_data);
			} else {
				$db_data['updated_at'] 	= date("Y-m-d H:i:s"); 
				DB::table('merchantglobal')->
					where('merchant_id', $user_data->company_id())->
					update($db_data);
			}

			$msg = "Data updated";
			return view("layouts.dialog", compact('msg'));

		} catch (\Exception $e) {
			\Log::error([
				"Error"	=>	$e->getMessage(),
				"File"	=>	$e->getFile(),
				"Line"	=>	$e->getLine()
			]);
			abort(404);
		}
	}
}
