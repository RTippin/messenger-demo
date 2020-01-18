<ul class="navbar-nav mr-auto">

</ul>
<ul class="navbar-nav mb-1">
    <li data-toggle="tooltip" title="Active Calls" data-placement="left" id="active_calls_nav" class="NS nav-item dropdown ml-1 mr-2 my-2 my-lg-0">
        <a href="#" role="button" aria-expanded="false" data-toggle="dropdown" class="dropdown-toggle glowing_warning_btn nav-link pt-1 pb-0 rounded text-center text-warning">
            <i class="fas fa-satellite-dish fa-2x"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right notify-drop">
            <div class="col-12 text-center">
                <h6 class="font-weight-bold">Active Calls</h6>
                <hr class="mt-n1 mb-2">
            </div>
            <div id="active_calls_ctnr" class="drop-content list-group"></div>
        </div>
    </li>
    <li class="nav-item mx-1 {{Request::is('messenger*') ? 'active' : ''}}">
        <a class="nav-link pt-1 pb-0" href="{{ route('messages') }}">
            <i class="fas fa-comment fa-2x"></i>
            <span id="nav_thread_count" class="badge badge-pill badge-danger badge-notify"></span>
        </a>
    </li>
    <li id="pending_friends_nav" class="nav-item dropdown mx-1 my-2 my-lg-0">
        <a id="click_friends_tab" href="#" class="dropdown-toggle nav-link pt-1 pb-0" data-toggle="dropdown" role="button" aria-expanded="false">
            <i class="fas fa-user-friends fa-2x"></i>
            <span id="nav_friends_count" class="badge badge-pill badge-danger badge-notify"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right notify-drop" aria-labelledby="click_friends_tab">
            <div class="col-12 text-center">
                <h6 class="font-weight-bold">Friend Request</h6>
                <hr class="mt-n1 mb-2">
            </div>
            <div id="pending_friends_ctnr" class="drop-content list-group">
                <div class="col-12 text-center h5 mt-2"><span class="badge badge-pill badge-secondary"><i class="fas fa-user-friends"></i> No Friend Request</span></div>
            </div>
            <div class="col-12 text-center mt-2 pb-4 pb-lg-3">
                <hr class="mb-1 mt-0">
                <span class="float-right"><a onclick="ThreadManager.load().search(); return false;" href="#"><i class="fas fa-search"></i> Find Friends</a></span>
            </div>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a id="user_nav_dp" href="#" class="dropdown-toggle nav-link pb-lg-0" data-toggle="dropdown" role="button" aria-expanded="false">
            <img class="rounded align-top my-n2 my-global-avatar" id="navProf_pic" height="38" width="38" src="{{messenger_profile()->avatar()}}">
            <i class="h5 fas fa-caret-down"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="user_nav_dp">
            <a class="dropdown-item {{Request::is('user/profile/'.messenger_profile()->slug()) ? 'active' : ''}}"  href="{{messenger_profile()->slug(true)}}"><i class="fas fa-user-circle"></i> Profile</a>
            <a onclick="TippinManager.forms().Logout(); return false;" class="dropdown-item"  href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </li>
</ul>
