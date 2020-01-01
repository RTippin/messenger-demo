<div class="col-12">
@if($networks->count())
    <label class="font-weight-bold control-label h5 mb-3">Add contacts to group:</label>
    @include('messenger.partials.groupConnections')
@else
    <h4 class="text-center my-5"><span class="badge badge-pill badge-secondary"><i class="fas fa-user-friends"></i> No contacts to add</span></h4>
@endif
</div>