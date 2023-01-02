<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use Auth;
use App\Models\Company;
use App\Models\Twoway;
use App\Models\MerchantLinkRelation;
use App\Models\MerchantLink;
use App\Models\Oneway;
use App\Models\MerchantRelation;
use App\Http\Controllers\ApiMessageController;
use App\Models\Merchant;

class DataManagementController extends Controller
{
    public function addNewDealer(Request $request)
    {   
        try{
            $companySystemId = $request->merchant_id;
            if(!$request->has('merchant_id') || !$request->merchant_id){
                return ( new ApiMessageController())->failedresponse(("Missing merchant_id parameter"));
            }
            $initiator_user_id = Auth::user()->id;
    
            
            $check_in_db = DB::select("select * from company where systemid = {$companySystemId}", [1]);

            $selfCompany = DB::select("select * from company where owner_user_id= {$initiator_user_id}", [1]);
            $selfCompany = $selfCompany[0];
            if( count($check_in_db) < 1 ){
                return (new ApiMessageController())->failedresponse('Sorry! This merchant does not exist.');
            }
           
            $responder_user_id = $check_in_db[0] ->owner_user_id;
            
            if( $responder_user_id === $initiator_user_id){
                return (new ApiMessageController())->failedresponse('Sorry! A merchant cannot add himself');
            }

            $self_merchant = Merchant::where("company_id",$selfCompany->id)->first();
            if(!$self_merchant){
                return (new ApiMessageController())->failedresponse('You are not allowed to use this feature.');
            }

            // CheckIf a merchant link exists.
            $doesRecordExists = MerchantLink::where("initiator_user_id", $initiator_user_id)
            ->orWhere("responder_user_id", $initiator_user_id)
            ->count();
            if( $doesRecordExists !== 0){
                return (new ApiMessageController())->failedresponse('Record already exists');
            }
            // Save it 
            
            $self_merchant = Merchant::where("company_id",$selfCompany->id)->first();
            
            $partner_merchant = Merchant::where("company_id", $check_in_db[0]->id)->first();
            if(!$partner_merchant){
                return (new ApiMessageController())->failedresponse('Invalid Merchant Id');
            }
          
            $twoway_save = TwoWay::create([
                'supplier_initiator_user_id' => $initiator_user_id,
                'supplier_responder_user_id' => $responder_user_id,
                'dealer_initiator_user_id' => $initiator_user_id,
                'dealer_responder_user_id' => $responder_user_id,
                'supplier_status' => 'pending',
                'dealer_status' => 'pending'
            ]);

            // Add to Merchant Link
            $merchantLink= MerchantLink::create([
                "initiator_user_id" => $initiator_user_id,
                "responder_user_id" => $responder_user_id,
                "status" => "linked"

            ]);

            // for two way add two relations in merchant link relation table 

            MerchantLinkRelation::create([
                'initiator_user_id' => $initiator_user_id,
                'responder_user_id' => $responder_user_id,
                'ptype' => 'supplier',
                'merchantlink_id' => $merchantLink->id,
                'status' => 'pending'
            ]);
            MerchantLinkRelation::create([
                'initiator_user_id' => $initiator_user_id,
                'responder_user_id' => $responder_user_id,
                'merchantlink_id' => $merchantLink->id,
                'ptype' => 'dealer',
                'status' => 'pending'
            ]);

            $query = "
            SELECT 
            c2.systemid as merchant_id,
            c2.name as business_name,
            c2.business_reg_no as business_reg_no,
            mrl.status as status,
            mrl.id as id,
            ml.initiator_user_id as initiator_user_id
            from merchantlinkrelation as mrl
            
            join merchantlink as ml on ml.id = mrl.merchantlink_id
            join company as c2 on c2.owner_user_id = ml.responder_user_id
            WHERE 
            ml.initiator_user_id = $initiator_user_id
           
            AND mrl.deleted_at IS NULL 
            AND ml.id = $merchantLink->id";

            $supplierQuery = $query."
                
                AND mrl.ptype= 'supplier'
                LIMIT 1
                
                
            ";
            
            $dealerQuery = $query." AND mrl.ptype= 'dealer'
            LIMIT 1 ";
                
            $ret = ["dealer"=>DB::select(DB::raw($dealerQuery)), "supplier"=>DB::select(DB::raw($supplierQuery))];
            return (new APIMessageController())->successResponse($ret, 'Your Request has been sent to your dealer!');

        }
        catch(Exception $e){
            return (new ApiMessageController())->failedresponse($e->getMessage());
        }
    }


    public function getSupplierData()
    {
        $user_id = Auth::user()->id;

        $query = "
            SELECT 

            mlr.status as status,
            mlr.id as id,
            ml.initiator_user_id,
            ml.responder_user_id,
            c2.systemid as merchant_id,
            c2.name as business_name,
            c2.id as merchant_real_id,
            c2.business_reg_no as business_reg_no
            from 

            merchantlinkrelation as mlr
            join merchantlink as ml on ml.id = mlr.merchantlink_id
            join company as c2 on c2.owner_user_id = ml.responder_user_id
            WHERE 

            mlr.status !=  'unlinked'
            AND 
                (CASE 
                WHEN ml.initiator_user_id = $user_id
                   THEN  mlr.ptype = 'supplier'
                ELSE 
                    mlr.ptype = 'dealer'
                END)
            AND mlr.deleted_at IS NULL


        ";
        $twoway = DB::select(DB::raw($query));
        $ret = [

            "twoway" => $twoway,
            "oneway" => []
        ];
        return (new APIMessageController())->successResponse($ret, 'success');

    }

    public function getDealerData()
    {
        $user_id = Auth::user()->id;
      

        $query =" 
        SELECT 
            
            mlr.status as status,
            mlr.id as id,
            ml.initiator_user_id,
            ml.responder_user_id,
            c2.systemid as merchant_id,
            c2.name as business_name,
            c2.business_reg_no as business_reg_no
            from 

            merchantlinkrelation as mlr
            join merchantlink as ml on ml.id = mlr.merchantlink_id
            join company as c2 on c2.owner_user_id = ml.responder_user_id
            WHERE 

            mlr.status !=  'unlinked'
            AND 
                (CASE 
                WHEN ml.initiator_user_id = $user_id
                   THEN  mlr.ptype = 'dealer'
                ELSE 
                    mlr.ptype = 'supplier'
                END)
            AND mlr.deleted_at IS NULL
            AND ml.deleted_at IS NULL
        ";
        // dd($query);
        $twoway = DB::select(DB::raw($query));
        $ret = [

            "twoway" => $twoway,
            "oneway" => []
        ];
        return (new APIMessageController())->successResponse($ret, 'success');
    }

    public function deleteTwoWay($id)
    {
        TwoWay::destroy($id);
        MerchantRelation::where("twoway_id",$id)->delete();
        return (new APIMessageController())->successResponse("", 'success');

    }

    
    public function statusUpdateTwoWay(Request $r)
    {
        $id = $r->id;
        $status = $r->action;
        $allowedStatuses = ["pending","active","rejected","inactive"];
        if( in_array($status, $allowedStatuses) === False){
            
            return (new APIMessageController())->forbiddenResponse("Invalid action done.");
        }
        $sourceTab = $r->sourceTab;
 
        $merchantLinkRelation = MerchantLinkRelation::find($id);

        $myUserId = Auth::user()->id;

        $mr = MerchantLink::where("id",$merchantLinkRelation->merchantlink_id)->first();

        if( !$mr ){
            return (ApiMessageController())->forbiddenResponse("No merchant relation found.");
        }

        $isResponder = false;
        if( $mr->initiator_user_id !== $myUserId
        ){
            $isResponder = true;
        }
        // Checks 

        if( $mr->status === "pending" && !$isResponder){
            return (new APIMessageController())->forbiddenResponse("");
        } elseif( $mr->status === "pending" &&  $isResponder){
            // Update TwoWay 
            $newStatus = "active";
            if( $status === "rejected"){
                $newStatus = "rejected";
            }
         
            

        }

        //Update MerchantRelation
        $newStatus = $status;
        $oldStatus = $merchantLinkRelation->status;

        // if( $sourceTab === "supplier" && $isResponder){
        //     $oldStatus = $mr->dealer_status;
        // }elseif( $sourceTab === "supplier" && !$isResponder){
        //     $oldStatus = $mr->supplier_status;
        // }elseif( $sourceTab === "dealer" && $isResponder){
        //     $oldStatus = $mr->supplier_status;
        // }elseif( $sourceTab === "dealer" && !$isResponder ){
        //     $oldStatus = $mr->dealer_status;
        // }
        
        // dump( $oldStatus);
        switch( $oldStatus ){
            
            
            case "inactive":
                
                $status = "pending";
                // Create new MerchantLink
                //Create new MerchantLinkRelation
                //Unlink old MerchantLinkRelation
                $initiator_user_id = $mr->initiator_user_id;
                $responder_user_id = $mr->responder_user_id;
                $ptype = $sourceTab;
                if( $isResponder){
                    $initiator_user_id = $mr->responder_user_id;
                    $responder_user_id = $mr->initiator_user_id;
                    if( $ptype === "supplier"){
                        $ptype = "dealer";
                    }elseif( $ptype === "dealer"){
                        $ptype = "supplier";
                    }
                    
                }
                $merchantLink= MerchantLink::create([
                    "initiator_user_id" => $initiator_user_id,
                    "responder_user_id" => $responder_user_id,
                    "status" => "linked"
    
                ]);
    
                // for two way add two relations in merchant link relation table 
    
                MerchantLinkRelation::create([
                    'initiator_user_id' => $initiator_user_id,
                    'responder_user_id' => $responder_user_id,
                    'ptype' => $sourceTab,
                    'merchantlink_id' => $merchantLink->id,
                    'status' => 'pending'
                ]);
                $newStatus = "unlinked";
                $merchantLinkRelation->status = $newStatus;
                $merchantLinkRelation->save();

            break;
            default:
            $merchantLinkRelation->status = $status;
            $merchantLinkRelation->save();
            break;

        }


        return (new APIMessageController())->successResponse($status, 'success');
  

    }

    function locations(){
        $ret = [];

        $user_id = Auth::user()->id;
        $company= Company::where("owner_user_id",$user_id)->first();

        $systemid = $company->systemid;
        $query = "SELECT id,branch ,default_initial_location from location where systemid=".$systemid." AND deleted_at IS NULL";
     
        $ret = DB::select(DB::raw($query));
        // dump($ret);
        return (new APIMessageController())->successResponse($ret,'success');
    }
    
}
