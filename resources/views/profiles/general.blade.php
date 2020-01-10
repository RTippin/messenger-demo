<div id="general_tab">
    <div class="row">
        <div class="col-12 mb-2">
            <div class="card bg-gradient-light text-dark">
                <div class="card-header border-0 py-1">
                    <i class="fas fa-info-circle"></i> About
                </div>
                <div class="card-body py-2 h5 mb-0">
                    <p class="lead">Welcome to Tippin's Laravel Messenger demo app! We will be adding documentation soon, and working towards
                        releasing this as an entire suite/package! The source code for this demo project can be found on
                        <a target="_blank" href="https://github.com/RTippin">Tippin's Github</a>. You may sign up anytime and test
                        out our features currently provided, such as real time messaging/video calling/screen sharing/group messaging,
                        etc. We also provide a list of pre-populated users to choose from on the login page.
                        We will reset this database and all uploaded files once each week</p>
                </div>
            </div>
        </div>
        <div class="col-12 mt-3">
            <div class="card bg-transparent border-0">
                <div class="card-header bg-transparent h4">
                    <i class="fas fa-network-wired"></i> Friends
                </div>
                <div class="card-body bg-transparent">
                    @if($profile->owner->networks->count())
                        <div class="table-responsive-sm">
                            <table id="user_networks_table" class="table table-sm table-hover table-striped rounded">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($profile->owner->networks as $network)
                                    <tr>
                                        <td>
                                            <div class="table_links">
                                                <div class="nowrap">
                                                    <a href="{{$network->party->slug(true)}}">
                                                        <img class="rounded group-image" src="{{$network->party->avatar()}}"/>
                                                        <span class="h5"><span class="badge badge-light">{{$network->party->name}}</span></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <h5 class="text-center">No networks to show</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
