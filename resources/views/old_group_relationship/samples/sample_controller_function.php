    public function saveMerchantTwoWayLinking(Request $request)
    {
        //$initiatorUserId = Auth::user()->id;
        $initiatorUserId = $this->getCompanyUserId();

        $responder = $request->input('merchant_id');
        $merchant = Company::where('systemid', $responder)->first();
        if ($merchant !== null) {
            $responderUserId = $merchant->owner_user_id;
            if ($initiatorUserId == $responderUserId) {
                return response()->json([
					'msg' => 'A merchant cannot add himself',
					'status' => 'false']);
            }

			Log::debug('TW responder='.$responder);
			Log::debug('TW responderUserId='.$responderUserId);
			Log::debug('TW initiatorUserId='.$initiatorUserId);

            $merchantLink = MerchantLink::
				where('responder_user_id', $responderUserId)->
				where('initiator_user_id', $initiatorUserId)->
				first();

			if (!empty($merchantLink)) {
				Log::debug('TW merchantLink='.json_encode($merchantLink));
				Log::debug('TW merchantLink->initiator_user_id='.
					$merchantLink->initiator_user_id);
				Log::debug('TW merchantLink->responder_user_id='.
					$merchantLink->responder_user_id);
			}

            if (!empty($merchantLink) &&
				$merchantLink->initiator_user_id == $initiatorUserId &&
				$merchantLink->responder_user_id == $responderUserId) {
                return response()->json([
					'msg' => 'Merchant ID already added',
					'status' => 'false'
				]);

            } else {
                // Add Two way linking
                $mLink = new MerchantLink();
                $mLink->initiator_user_id = $initiatorUserId;
                $mLink->responder_user_id = $responderUserId;
                $mLink->save();
                return response()->json([
					'msg' => 'Merchant ID added successfully',
					'status' => 'true'
				]);
            }

        } else {
            return response()->json([
				'msg' => 'Merchant ID not found',
				'status' => 'false'
			]);
        }
    }

