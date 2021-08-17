<div class="row mt-3 bg-gradient-dark">
    <div class="col-12 pill-tab-nav mt-2">
        <nav class="nav nav-pills flex-column flex-sm-row">
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.index') ? 'active' : ''}}" href="{{ route('docs.index') }}">Readme</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.install') ? 'active' : ''}}" href="{{ route('docs.install') }}">Installation</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.config') ? 'active' : ''}}" href="{{ route('docs.config') }}">Configuration</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.commands') ? 'active' : ''}}" href="{{ route('docs.commands') }}">Commands</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.broadcasting') ? 'active' : ''}}" href="{{ route('docs.broadcasting') }}">Broadcasting</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.bots') ? 'active' : ''}}" href="{{ route('docs.bots') }}">Chat Bots</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.calling') ? 'active' : ''}}" href="{{ route('docs.calling') }}">Calling</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.composer') ? 'active' : ''}}" href="{{ route('docs.composer') }}">Composer</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.helpers') ? 'active' : ''}}" href="{{ route('docs.helpers') }}">Helpers</a>
        </nav>
    </div>
</div>
