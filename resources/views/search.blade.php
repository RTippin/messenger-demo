@extends('layouts.app')
@section('seo')
	@include('seo.search')
@endsection
@section('content')
<div class="container-fluid">
	<div class="pb-2 mb-2 border-bottom">
		<div class="media">
			<div class="media-left media-top">
				<img src="{{asset('images/tipz.png')}}" height="75" width="75" class="rounded media-object">
			</div>
			<div class="media-body">
				<div class="col-12">
					<span class="h3 page-header">Locate Profiles</span><br/>
					<span class="h6 page-header"><strong>Search</strong> <i class="fas fa-search"></i></span>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div class="col-12 px-0 mb-5">
		<div class="card shadow">
			<div class="card-header px-0">
				<form id="search_page_form" class="form-inline" method="GET" action="{{ route('search') }}">
					<div class="col-12">
						<div class="input-group">
							<input name="query" class="form-control" placeholder="Search for People" value="{{ Request('query') }}" autofocus>
							<div class="input-group-append">
								<button type="submit" id="searchBtn" class="btn btn-md btn-info"><i class="fas fa-search"></i></button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
@if(!Request::has('query'))
	<div class="col-12 px-0 text-center text-dark">
		<h3><i class="fas fa-poll-h"></i> Search for people</h3>
	</div>
@elseif(!count($users))
	<div class="col-12 px-0 text-center text-dark mt-5">
		<h3><i class="fas fa-poll-h"></i> No results found for '{{ Request('query') }}'</h3>
		<h4>Please try another query</h4>
	</div>
@else
	<div class="col-12 col-lg-8 offset-lg-2 mt-3 px-0">
	@if(count($users))
		<div class="card bg-transparent">
			<div class="card-header bg-gradient-secondary text-light py-1">
				<span class="h4"><strong><i class="fas fa-users"></i> People</strong></span>
			</div>
			<div class="card-body">
				<div class="table-responsive-sm">
					<table id="search_users_table" class="table table-sm table-hover table-striped">
						<thead>
						<tr>
							<th>Name</th>
							@if(Auth::check())<th></th>@endif
						</tr>
						</thead>
						<tbody>
						@foreach($users as $key => $item)
							<tr>
								<td>
									<div class="table_links">
										<div class="nowrap">
											<a href="{{$item->slug(true)}}">
												<img class="rounded group-image" src="{{asset($item->avatar())}}"/>
												<span class="h5"><span class="badge badge-light">{{$item->name}}</span></span>
											</a>
										</div>
									</div>
								</td>
								@if(Auth::check())
								<td>
									@if($user->id !== $item->id)
										@php $con = $current_model->networkStatus($item); @endphp
										<div class="float-right">
									<span id="network_for_{{$item->id}}">
									@switch($con)
											@case(0)
											<button id="add_network_{{$item->id}}" data-toggle="tooltip" title="Add friend" data-placement="top" class="btn btn-success pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'add', slug : '{{$item->slug()}}', type : 'user', owner_id : '{{$item->id}}'});"><i class="fas fa-user-plus fa-2x"></i></button>
											@break
											@case(1)
											<button id="remove_network_{{$item->id}}" data-toggle="tooltip" title="Remove friend" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'remove', slug : '{{$item->slug()}}', type : 'user', owner_id : '{{$item->id}}'});"><i class="fas fa-user-times fa-2x"></i></button>
											@break
											@case(2)
											<button id="cancel_network_{{$item->id}}" data-toggle="tooltip" title="Cancel friend request" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'cancel', slug : '{{$item->slug()}}', type : 'user', owner_id : '{{$item->id}}'});"><i class="fas fa-ban fa-2x"></i></button>
											@break
											@case(3)
											<button id="accept_network_{{$item->id}}" data-toggle="tooltip" title="Accept friend request" data-placement="top" class="btn btn-success pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'accept', slug : '{{$item->slug()}}', type : 'user', owner_id : '{{$item->id}}'});"><i class="far fa-check-circle fa-2x"></i></button>
											<button id="deny_network_{{$item->id}}" data-toggle="tooltip" title="Deny friend request" data-placement="top" class="btn btn-danger pt-1 pb-0 px-2" onclick="NetworksManager.action({action : 'deny', slug : '{{$item->slug()}}', type : 'user', owner_id : '{{$item->id}}'});"><i class="fas fa-ban fa-2x"></i></button>
											@break
										@endswitch
									</span>
											<a href="{{$item->slug(true)}}/message" data-toggle="tooltip" title="Message {{$item->name}}" data-placement="right" class="btn btn-primary pt-1 pb-0 px-2"><i class="fas fa-comments fa-2x"></i></a>
										</div>
									@endif
								</td>
								@endif
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	@endif
	</div>
@endif
</div>
@endsection
@push('special-js')
	<script>
		var people_table = $("#search_users_table");
		if(people_table.length) people_table.DataTable();
		PageListeners.listen().animateLogo({elm : '#RTlog'});
		$("#search_page_form").on("submit", function(){
			TippinManager.button().addLoader({id : '#searchBtn'});
		});
	</script>
@endpush
