<div class="modal fade" id="delivermanModel"  tabindex="-1" 
	role="dialog" aria-labelledby="staffNameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered  mw- 75 w- 50" role="document">
            <div class="modal-content modal---inside bg-greenlobster" >
		<div class="modal-header" >
			<h3 class="modal-title text-white"  id="statusModalLabel">Deliveryman</h3>
            	</div>
		<div class="modal-body">
			<ul style="padding:0;margin:0;list-style: none">
				@foreach($deliveryman_list as $d)
					<li style='cursor:pointer;' onclick="select_deliveryman({{$d->id}}, {{$fKey}})" 
						class="{{$d->id == $select_id ? 'active_deliveryman':''}}"><strong>{{$d->name}}</strong></li>
				@endforeach
			</ul>
		</div>
		</ul>
                <!-- div class="modal-footer" style="border:0;"> 
                </div --->
            </div>
        </div>

    </div>
 
