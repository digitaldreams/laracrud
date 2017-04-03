<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{asset('img/default-avatar.png')}}" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{auth()->user()->name}}</p>
                <!-- Status -->
                <a href="#">
                    Anything
                </a>
            </div>
        </div>

        <!-- search form (Optional) -->
        <form method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                <button type="submit" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <!-- Optionally, you can add icons to the links
                <li class="{{(stripos(request()->path(),'users')!==false)?'active':''}}">
                    <a href="{{route('sectors.index')}}">
                        <i class="fa fa-map-marker"></i>
                        <span>Users</span></a>
                </li>
             -->
            <li class="{{(stripos(request()->path(),'profile')!==false)?'active':''}}">
                <a href="{{route('show.user.profile')}}">
                    <i class="fa fa-user"></i>
                    <span>@lang('labels.profile')</span></a>
            </li>
            <li>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();"
                   class=""><i class="fa fa-sign-out"></i>
                    <span>@lang('labels.signOut')</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </li>
        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>