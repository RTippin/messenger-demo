<div class="row mt-3 bg-gradient-dark">
    <div class="col-12 pill-tab-nav mt-2">
        <nav class="nav nav-pills flex-column flex-sm-row">
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->routeIs('docs.index') ? 'active' : ''}}" href="{{ route('docs.index') }}">Readme</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Installation.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Installation.md') }}">Installation</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Configuration.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Configuration.md') }}">Configuration</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Commands.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Commands.md') }}">Commands</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Broadcasting.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Broadcasting.md') }}">Broadcasting</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Events.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Events.md') }}">Events</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*ChatBots.md') ? 'active' : ''}}" href="{{ route('docs.render', 'ChatBots.md') }}">Chat Bots</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Calling.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Calling.md') }}">Calling</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Composer.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Composer.md') }}">Composer</a>
            <a class="flex-sm-fill text-sm-center nav-link h4 {{request()->is('*Helpers.md') ? 'active' : ''}}" href="{{ route('docs.render', 'Helpers.md') }}">Helpers</a>
        </nav>
    </div>
</div>
