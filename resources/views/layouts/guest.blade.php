<ul class="navbar-nav mr-auto">
    <li class="nav-item" >
        <a class="nav-link" target="_blank" href="https://github.com/RTippin/messenger">Github <i class="fab fa-github"></i></a>
    </li>
    <li class="nav-item {{request()->is('config') ? 'active' : ''}}" >
        <a class="nav-link" href="{{route('config')}}">Config <i class="fas fa-server"></i></a>
    </li>
    <li class="nav-item {{request()->is('api-explorer') ? 'active' : ''}}" >
        <a class="nav-link" href="{{route('api-explorer.index')}}">API Explorer <i class="fas fa-laptop-code"></i></a>
    </li>
    <li class="nav-item {{request()->is('docs*') ? 'active' : ''}}" >
        <a class="nav-link" href="{{route('docs.index')}}">Documentation <i class="fab fa-readme"></i></a>
    </li>
</ul>
<ul class="navbar-nav">
    <li class="nav-item {{request()->route() && request()->route()->getName() === 'register' ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('register') }}">Sign Up <i class="fas fa-user-plus"></i></a>
    </li>
    <li class="nav-item {{request()->route() && request()->route()->getName() === 'login' ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('login') }}">Log In <i class="fas fa-sign-in-alt"></i></a>
    </li>
</ul>
