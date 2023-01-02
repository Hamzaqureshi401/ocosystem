<?php

namespace App\Http\Controllers;

use App\Models\combine;
use App\Models\location;
use Illuminate\Http\Request;
use App\Models\openBill;
use App\Models\openBillProduct;
use App\Models\opos_receipt;
use App\Models\oposFtype;
use App\Models\platopenbillproductspecial;
use App\Models\reserve;
use App\Models\splitTable;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Matrix\Exception;
use App\Models\opos_tablename;

use \App\Models\locationterminal;

use \App\Models\terminal;
use \App\Models\prd_special;
use \App\Models\productspecial;
use Log;
class PlatSplitCombineReserveController extends Controller
{
    //
    public function reserveTable(Request $request){
        try{
            $type = $request->get('tableType');
            $number =   $request->get('tableNumber');
            $terminal_id = $request->get('terminal_id');
            $message = $request->get('message');
            $allInputs = $request->all();
            $reserve = false;

            $validation = Validator::make($allInputs, [
                'tableType'         => 'required',
                'tableNumber'   => 'numeric',
                'terminal'    => 'numeric',
            ]);
            if ($validation->fails()) {
                throw new Exception("invalid_input", 1);
            }
            $terminal = terminal::where('systemid',$terminal_id)->value('id');
            if (!$terminal) {
                throw new Exception("terminal_not_found", 1);
            }
            $location_id = locationterminal::where('terminal_id',$terminal)->value('location_id');

            if(is_numeric($number)){
                $ftype_id = oposFtype::join('plat_reserve','plat_reserve.ftype_id','=','opos_ftype.id')
                    ->where('fnumber',$number)
                    ->where('plat_reserve.status','active')
                    ->where('terminal_id',$terminal)
                    ->whereDay('opos_ftype.created_at', '=', date('d'))
                    ->whereNull('opos_ftype.deleted_at')
                    ->first();
                if(empty($ftype_id)){
                    $ftype = new oposFtype();
                    $ftype->fnumber = $number;
                    $ftype->location_id = $location_id;
                    $ftype->terminal_id = $terminal;
                    $ftype->ftype = $type;
                    $ftype->description = 'Reserved';
                    $ftype->name = 'Reserved';
                    $ftype->save();

                    $plat_reserve = new reserve();
                    $plat_reserve->ftype_id = $ftype->id;
                    $plat_reserve->status = 'active';
                    $plat_reserve->message = $message;
                    $msg = "Table reserved successfully";
                    $reserve = true;
                    $plat_reserve->save();
                }else{
                    reserve::where('id',$ftype_id->id)->update(['message' => $message]);
                    $reserve = true;
                    $msg = "Table updated successfully";
                }

            }

            if ($reserve == true) {
                $response = view('layouts.purpledialog', compact('msg'));
            } else {
                $msg ="An Error Occurred";
                $response = view('layouts.purpledialog', compact('msg'));
            }
        } catch (\Exception $e){
            Log::debug($e->getMessage());
            if ($e->getMessage() == 'invalid_input') {
                $msg = "Invalid input";
            }else if ($e->getMessage() == 'terminal_not_found') {
                $msg = "Terminal not found";
            } else {
                $msg = "Some error occured";
            }
            $response = view('layouts.purpledialog', compact('msg'));
        }
        return $response;
    }

    public function splitTable(Request $request){
        try{
            $type = $request->get('tableType');
            $number =   $request->get('tableNumber');
            $terminal_id = $request->get('terminal_id');

            $allInputs = $request->all();
            $split = false;

            $validation = Validator::make($allInputs, [
                'tableType'         => 'required',
                'tableNumber'   => 'numeric',
                'terminal'    => 'numeric'
            ]);
            if ($validation->fails()) {
                throw new Exception("invalid_input", 1);
            }

            $terminal = terminal::where('systemid',$terminal_id)->value('id');
            if (!$terminal) {
                throw new Exception("terminal_not_found", 1);
            }
            $location_id = locationterminal::where('terminal_id',$terminal)->value('location_id');

            if(is_numeric($number)){
                $plat_split = new splitTable();
                $plat_split->terminal_id = $terminal;
                $plat_split->location_id = $location_id;
                //  $plat_split->ftype_id = 1;
                $plat_split->status = 'pending';
                $plat_split->split_fnumber = $this->generateSplit($number, $location_id);
                if ($this->generateSplit($number, $location_id) == 0) {
                    throw new Exception("split_exceeded", 1);
                }
                $split = true;
            }

            if ($split == true) {
                $plat_split->save();

                $msg = "Table split successfully";
                $response = view('layouts.purpledialog', compact('msg'));
            } else {
                $response  = null;
            }


        } catch (\Exception $e){
            Log::debug($e->getMessage());
            if ($e->getMessage() == 'invalid_input') {
                $msg = "Cannot split takeaway table";
            }else if ($e->getMessage() == 'invalid_quantity') {
                $msg = "Invalid Quantity";
            }else if ($e->getMessage() == 'terminal_not_found') {
                $msg = "Terminal not found";
            }else if ($e->getMessage() == 'split_exceeded') {
                $msg = "Split number exceeded";
            } else {
                $msg = "Some error occured";
            }
            $response = view('layouts.purpledialog', compact('msg'));
        }
        return $response;
    }

    public function showCombineTable($terminal_id){
        try{
            $terminal_id = terminal::where('systemid',$terminal_id)->first();

            $locationterminal = locationterminal::where('terminal_id', $terminal_id->id)->first();
            $location = location::find($locationterminal->location_id);
            $tablename = opos_tablename::where('location_id', $location->id)->get();
            $combined_tables_plus = combine::join('opos_ftype','plat_combine.combine_ftype_id','=','opos_ftype.id')->
            where('opos_ftype.location_id',$location->id)->
            where('plat_combine.status','active')->
            whereDay('opos_ftype.created_at', '=', date('d'))->
            pluck('fnumber')->toArray();
            $opossum_cont = new OpossumController();
            $combined_tables = $opossum_cont->getCombinedTables($combined_tables_plus);
            $reserved_tables = reserve::join('opos_ftype','opos_ftype.id','=','plat_reserve.ftype_id')->
                                        where('opos_ftype.location_id',$location->id)->
                                        where('status','active')->pluck('fnumber')->toArray();

            $active_tables = oposFtype::join('plat_openbill','plat_openbill.ftype_id','=','opos_ftype.id')->
            join('plat_openbillproduct','plat_openbillproduct.openbill_id','=','plat_openbill.id')->
            where('plat_openbill.status','active')->
            where('opos_ftype.location_id',$location->id)->
            whereDay('opos_ftype.created_at', '=', date('d'))->
            pluck('fnumber')->toArray();
            Log::debug("active_tables".json_encode($active_tables));
            $disabled_tables_for_combine = array_merge($reserved_tables,$active_tables,$combined_tables);
            $html = '';
            $html .='<div class="row" style="text-align: center;margin-left: 12px;">';
					for($i=1; $i<=200; $i++){
					    $name = $this->table_name($i, $tablename);
                    $disabled = $this->disable_combine($i, $disabled_tables_for_combine)? 'disabled' : '';

             $html .= '<button id="combineTable'.$i.'" onclick="combineTables('.$i.')" class="btn btn-success btn-log bg-opos_combinebtn opos_combinebtn"'.$disabled.' >';
                $html .='<span>'.$name.'</span></button>';

                    }


		$html.='</div>';

            return $html;
        }catch (\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }

    }

    function table_name($table_number, $table) {
        foreach ($table as $f) {
            if ($f->default_name == $table_number) {
                if ($f->new_name != null || $f->new_name != '') {
                    return $f->new_name;
                }
            }
        }
        return $table_number;
    }

    function showReserveTable($tableNumber){
        try{
            $reserve_message = reserve::join('opos_ftype','plat_reserve.ftype_id','=','opos_ftype.id')->
                                        where('status','active')->
                                        where('fnumber',$tableNumber)->value('message');
            if(!empty($reserve_message)){
                return $reserve_message;
            }else{
                return '';
            }

        }catch (\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }

    function disable_combine($tableNumber,$disabled_tables_for_combine){
            $disabled_tables_for_combine = $this->stringToIntegerArray($disabled_tables_for_combine);
        if(in_array((int)$tableNumber, $disabled_tables_for_combine)){
            return true;
        }else{
            return false;
        }
    }

    function stringToIntegerArray($array){
        $result = array_filter($array, function ($item) {
            if ((is_numeric($item))) {
                return true;
            }
            return false;
        });
        return $result;
    }


    public function generateSplit($number, $location_id)
    {
        $letter = range("a", "z");
        $activeSplits = splitTable::whereIn('split_fnumber',[
			$number.'a',$number.'b',$number.'c'])->where('location_id',$location_id)
			->whereIn('status', ['pending', 'active'])->count();

        $split_nos = 0;
        if ($activeSplits >= 3) {
            return $split_nos;
        } else {
            for ($i = 0; $i < count($letter); $i++) {
                $split_nos = $number . $letter[$i];
                $exists = splitTable::where('split_fnumber', $split_nos)
                    ->whereIn('status',['pending','active'])
                    ->where('location_id',$location_id)
                    ->whereNull("deleted_at")->first();
                if (empty($exists)) {
                    return $split_nos;
                    break;
                }
            }
        }
    }

    function showReserve(Request $request){
        try{
            $terminal = terminal::where('systemid', $request->terminal_id)->first();


            $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
            // $terminal_valid = terminal::pluck('id');
            // $terminal_ids = locationterminal::where('location_id',$location_id)->whereIn('terminal_id',$terminal_valid)->pluck('terminal_id');

            $ftype_id = oposFtype::join('plat_reserve','plat_reserve.ftype_id','=','opos_ftype.id')
                ->where('fnumber',$request->tableNumber)
                ->where('plat_reserve.status','active')
                ->where('opos_ftype.location_id', $location_id)
                ->whereDay('opos_ftype.created_at', '=', date('d'))
                ->whereNull('opos_ftype.deleted_at')
                ->orderby('opos_ftype.id','desc')
                ->first();

            if (empty($ftype_id)) {
                throw new Exception("ftype_id_not_found", 1);

            }else{

                return $ftype_id;
            }
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }

    function deleteReserveTable(Request $request){
        try{
            $terminal = terminal::where('systemid', $request->terminal_id)->first();

            $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
            // $terminal_valid = terminal::pluck('id');
            // $terminal_ids = locationterminal::where('location_id',$location_id)->whereIn('terminal_id',$terminal_valid)->pluck('terminal_id');

            $ftype_id = oposFtype::join('plat_reserve','plat_reserve.ftype_id','=','opos_ftype.id')
                ->where('fnumber',$request->tableNumber)
                ->where('plat_reserve.status','active')
                ->where('opos_ftype.location_id', $location_id)
                ->whereDay('opos_ftype.created_at', '=', date('d'))
                ->whereNull('opos_ftype.deleted_at')
                ->orderby('opos_ftype.id','desc')
                ->first();

            if (empty($ftype_id)) {
                throw new Exception("ftype_id_not_found", 1);
            }else{
                $opos_deleted =   oposFtype::where('id',$ftype_id->ftype_id)->update(['deleted_at'=> Carbon::now()]);
                $reserve_deleted =   reserve::where('id',$ftype_id->id)->update(['deleted_at' => Carbon::now(),'status' => 'cancelled']);

                if($opos_deleted && $reserve_deleted){
                    $msg = 'Reserve successfully cancelled';
                    $response = view('layouts.purpledialog', compact('msg'));
                }else{
                    $response = null;
                }
                return $response;
            }
        }catch (\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }

    function cancelTable(Request $request){
        try{
            $terminal = terminal::where('systemid', $request->terminal_id)->first();
            $tableNumber = $request->tableNumber;

            $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
            // $terminal_valid = terminal::pluck('id');
            // $terminal_ids = locationterminal::where('location_id',$location_id)->whereIn('terminal_id',$terminal_valid)->pluck('terminal_id');


            $ftype_id = oposFtype::where('fnumber',$tableNumber)->
                where('location_id', $location_id)
                ->whereDay('created_at', '=', date('d'))
                ->whereNull('deleted_at')
                ->orderBy('id','desc')
                ->first();
            Log::debug(' $ftype_id'. json_encode($ftype_id));
            if (!empty($ftype_id)) {
                // $terminal = terminal::where('id', $ftype_id->terminal_id)->first();
                $plat_openbill = openBill::where('ftype_id',$ftype_id->id)->where('status', '!=','voided')->first();
                if(!empty($plat_openbill)){
                    $plat_products = openBillProduct::where('openbill_id',$plat_openbill->id)->
                    where('status', '!=','voided')
                        ->get();
                    if(!empty($plat_products)){
                        foreach ($plat_products as $plat_product){
                            $plat_product->status = 'voided';
                            $plat_product->save();
                        }
                            $plat_openbill->status = 'voided';
                            $plat_openbill->save();
                            $ftype_id->deleted_at = Carbon::now();
                            $ftype_id->save();

                            if($ftype_id->name == 'Split'){
                                $split_table = splitTable::where('split_fnumber',$tableNumber)->where('location_id', $location_id)->whereIn('status',array('active','pending'))->orderby('id','desc')->first();
                                $split_table->status = 'void';
                                $split_table->save();
                            }else if($ftype_id->name == 'Combined'){
                                $combine_table = combine::where('combine_ftype_id', $ftype_id->id)->whereIn('status', ['pending','active'])->first();
                                if(!empty($combine_table)){
                                    $combine_table->status = 'cancelled';
                                    $combine_table->save();
                                }

                            }

                    }

                }else{
                    Log::debug('Open bill not found $ftype_id'. json_encode($ftype_id));
                    $combine_table = combine::where('combine_ftype_id', $ftype_id->id)->whereIn('status', ['pending','active'])->first();
                    if(!empty($combine_table)){
                        $combine_table->status = 'cancelled';
                        $combine_table->save();
                    }
                    $ftype_id->deleted_at = Carbon::now();
                    $ftype_id->save();
                }
                $msg = 'Table successfully cancelled';
                $response = view('layouts.purpledialog', compact('msg'));
            }else{
                $split_table = splitTable::where('split_fnumber',$tableNumber)
                ->where('location_id', $location_id)->whereIn('status',array('active','pending'))->orderby('id','desc')->first();
                if(!empty($split_table)){
                    $split_table->status = 'void';
                    $split_table->save();
                    $msg = 'Table successfully cancelled';
                    $response = view('layouts.purpledialog', compact('msg'));
                }else{
                    $msg = 'Table is not created on this terminal';
                    $response = view('layouts.purpledialog', compact('msg'));
                }

            }
            return $response;
        }catch (\Exception $e){
            Log::error(
                "Error @ " . $e->getLine() . " file " . $e->getFile() . ":" . $e->getMessage()
            );
        }
    }

    public function combineTable(Request $request){
        try{
            $combination = $request->get('combination');
            $no_of_elements = count($combination);
            $firstElement = '';
           if($no_of_elements > 3){
               $msg = 'Combine table limit exceeded';
               $response = view('layouts.dialog', compact('msg'));
           }else if($no_of_elements > 1){
               for($i = 0; $i < $no_of_elements; $i++){
                   if(($no_of_elements - 1) > $i){
                       $firstElement .= $combination[$i]."+";
                   }else{
                       $firstElement .= $combination[$i];
                   }
               }
               $terminal = terminal::where('systemid', $request->terminal_id)->first();
               $location_id = locationterminal::where('terminal_id',$terminal->id)->value('location_id');
               $ftype = new oposFtype();
               $ftype->fnumber = $firstElement;
               $ftype->location_id = $location_id;
               $ftype->terminal_id = $terminal->id;
               $ftype->name = 'Combined';
               $ftype->description = 'Combined';
               $ftype->ftype = 'table';
               $ftype->save();

                   $plat_combine = new combine();
                   $plat_combine->terminal_id = $terminal->id;
                   $plat_combine->location_id = $location_id;
                   $plat_combine->pax = 0;
                   $plat_combine->combine_ftype_id = $ftype->id;
                   $plat_combine->status = 'active';
                   $plat_combine->save();


               $msg = 'Table combined successfully';
               $response = view('layouts.purpledialog', compact('msg'));
           }else{
               $msg = 'Table must be two to three tables';
               $response = view('layouts.purpledialog', compact('msg'));
           }
            return $response;
        }catch (\Exception $e){
            Log::debug($e->getMessage());
        }
    }
}
