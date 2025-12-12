<div class="sidebar-contact ">
    <div class="toggle-theme"  data-bs-toggle="offcanvas" data-bs-target="#theme-setting"><i class="fa fa-cog fa-w-16 fa-spin"></i></div>
    </div>
    <div class="sidebar-themesettings offcanvas offcanvas-end" id="theme-setting">
    <div class="offcanvas-header d-flex align-items-center justify-content-between bg-dark">
        <div>
            <h3 class="mb-1 text-white">Theme Customizer</h3>
            <p class="text-light">Choose your themes & layouts etc.</p>
        </div>
        <a href="#" class="custom-btn-close d-flex align-items-center justify-content-center text-white"  data-bs-dismiss="offcanvas"><i class="ti ti-x"></i></a>
    </div>
    <div class="themecard-body offcanvas-body">
        <div class="accordion accordion-customicon1 accordions-items-seperate" id="settingtheme">
            <div class="accordion-item border px-3 layout-select">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark bg-transparent fs-16 px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#layoutsetting" aria-expanded="true" aria-controls="collapsecustomicon1One">
                        Select Layouts
                    </button>
                </h2>
                <div id="layoutsetting" class="accordion-collapse collapse show"  >
                    <div class="accordion-body border-top px-0 py-3">
                        <div class="row gx-3">
                            <div class="col-4">
                                <div class="theme-layout mb-3">
                                    <input type="radio" name="LayoutTheme" id="defaultLayout" value="default" checked>
                                    <label for="defaultLayout">
                                        <span class="d-block mb-2 layout-img">
                                            <img src="{{URL::asset('build/img/theme/default.svg')}}" alt="img">
                                        </span>                                     
                                        <span class="layout-type">Default</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="theme-layout mb-3">
                                    <input type="radio" name="LayoutTheme" id="miniLayout" value="mini" >
                                    <label for="miniLayout">
                                        <span class="d-block mb-2 layout-img">
                                            <img src="{{URL::asset('build/img/theme/mini.svg')}}" alt="img">
                                        </span>                                    
                                        <span class="layout-type">Mini</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="theme-layout mb-3">
                                    <input type="radio" name="LayoutTheme" id="twocolumnLayout" value="twocolumn" >
                                    <label for="twocolumnLayout">
                                        <span class="d-block mb-2 layout-img">
                                            <img src="{{URL::asset('build/img/theme/two-column.svg')}}" alt="img">
                                        </span>                                    
                                        <span class="layout-type">Two Column</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="theme-layout mb-3">
                                    <input type="radio" name="LayoutTheme" id="horizontalLayout" value="horizontal" >
                                    <label for="horizontalLayout">
                                        <span class="d-block mb-2 layout-img">
                                            <img src="{{URL::asset('build/img/theme/horizontal.svg')}}" alt="img">
                                        </span>                                    
                                        <span class="layout-type">Horizontal</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="theme-layout mb-3">
                                    <input type="radio" name="LayoutTheme" id="detachedLayout" value="detached" >
                                    <label for="detachedLayout">
                                        <span class="d-block mb-2 layout-img">
                                            <img src="{{URL::asset('build/img/theme/detached.svg')}}" alt="img">
                                        </span>                                    
                                        <span class="layout-type">Detached</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="theme-layout mb-3">
                                    <input type="radio" name="LayoutTheme" id="without-headerLayout" value="without-header" >
                                    <label for="without-headerLayout">
                                        <span class="d-block mb-2 layout-img">
                                            <img src="{{URL::asset('build/img/theme/without-header.svg')}}" alt="img">
                                        </span>                                    
                                        <span class="layout-type">Without Header</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-4">
                                <a href="{{url('layout-rtl')}}" class="theme-layout mb-3">
                                    <span class="d-block mb-2 layout-img">
                                        <img src="{{URL::asset('build/img/theme/rtl.svg')}}" alt="img">
                                    </span>                                    
                                    <span class="layout-type d-block">RTL</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="accordion-item border px-3 layout-select">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark fs-16 bg-transparent px-0 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarsetting" aria-expanded="true">
                        Layout Width
                    </button>
                </h2>
                <div id="sidebarsetting" class="accordion-collapse collapse show">
                    <div class="accordion-body px-0 py-3 border-top">
                        <div class="d-flex align-items-center">
                            <div class="theme-width m-1 me-2">
                                <input type="radio" name="width" id="fluidWidth" value="fluid" checked>
                                <label for="fluidWidth" class="d-block rounded fs-12">
                                    <i class="ti ti-layout-list me-1"></i>
                                    Fluid Layout
                                </label>
                            </div>
                            <div class="theme-width m-1">
                                <input type="radio" name="width" id="boxWidth" value="box">
                                <label for="boxWidth" class="d-block rounded fs-12">
                                 <i class="ti ti-layout-distribute-horizontal me-1"></i>
                                    Boxed Layout
                                </label>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
            <div class="accordion-item border px-3">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark fs-16 px-0 py-3 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#topbarsetting" aria-expanded="true">
                        Top Bar Color
                    </button>
                </h2>
                <div id="topbarsetting" class="accordion-collapse collapse show"	>
                    <div class="accordion-body pb-1 px-0 py-3 border-top">
                       <p class="mb-2 text-gray-9">Solid Colors</p>
                       <div class="d-flex align-items-center flex-wrap">
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="whiteTopbar" value="white" checked>
                                <label for="whiteTopbar" class="bg-white"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="darkaquaTopbar" value="topbarcolorone">
                                <label for="darkaquaTopbar" class=" bg-sidebar-color-1"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="whiterockTopbar" value="topbarcolortwo">
                                <label for="whiterockTopbar" class=" bg-sidebar-color-2"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="rockblueTopbar" value="topbarcolorthree">
                                <label for="rockblueTopbar" class=" bg-sidebar-color-3"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="bluehazeTopbar" value="topbarcolorfour">
                                <label for="bluehazeTopbar" class=" bg-sidebar-color-4"></label>
                            </div>              
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-5" value="topbarcolorfive">
                                <label for="topbar-color-5" class="bg-sidebar-color-5"></label>
                            </div>              
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-6" value="topbarcolorsix">
                                <label for="topbar-color-6" class="bg-sidebar-color-6"></label>
                            </div>              
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 mt-0">
                                <div class="theme-topbar"></div>
                                <div class="pickr-topbar"></div>
                            </div>
                        </div>
                        <p class="mb-2 text-gray-9">Gradient Colors</p>
                       <div class="d-flex align-items-center flex-wrap">
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-7" value="topbarcolorseven">
                                <label for="topbar-color-7" class="bg-sidebar-color-7"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-8" value="topbarcoloreight">
                                <label for="topbar-color-8" class=" bg-sidebar-color-8"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-9" value="topbarcolornine">
                                <label for="topbar-color-9" class=" bg-sidebar-color-9"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-10" value="topbarcolorten">
                                <label for="topbar-color-10" class=" bg-sidebar-color-10"></label>
                            </div>
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-11" value="topbarcoloreleven">
                                <label for="topbar-color-11" class=" bg-sidebar-color-11"></label>
                            </div> 
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-12" value="topbarcolortwelve">
                                <label for="topbar-color-12" class=" bg-sidebar-color-12"></label>
                            </div> 
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-13" value="topbarcolorthirteen">
                                <label for="topbar-color-13" class=" bg-sidebar-color-13"></label>
                            </div> 
                            <div class="theme-colorselect theme-colorselect-rounded mb-3 me-3">
                                <input type="radio" name="topbar" id="topbar-color-14" value="topbarcolorfourteen">
                                <label for="topbar-color-14" class=" bg-sidebar-color-14"></label>
                            </div> 
                        </div>
                    </div>
                </div>
            </div> 
            <div class="accordion-item border px-3 layout-select">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark fs-16 px-0 py-3 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarcolor" aria-expanded="true">
                        Sidebar Color
                    </button>
                </h2>
                <div id="sidebarcolor" class="accordion-collapse collapse show">
                    <div class="accordion-body px-0 py-3 border-top">
                       <p class="mb-2 text-gray-9">Solid Colors</p>
                       <div class="d-flex align-items-center">
                            <div class="theme-colorselect m-1 me-3">
                                <input type="radio" name="sidebar" id="lightSidebar" value="light" checked>
                                <label for="lightSidebar" class="d-block mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolorSidebar" value="sidebarcolorone">
                                <label for="bgcolorSidebar" class="d-block bg-sidebar-color-1 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor2Sidebar" value="sidebarcolortwo">
                                <label for="bgcolor2Sidebar" class="d-block bg-sidebar-color-2 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor3Sidebar" value="sidebarcolorthree">
                                <label for="bgcolor3Sidebar" class="d-block bg-sidebar-color-3 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor4Sidebar" value="sidebarcolorfour">
                                <label for="bgcolor4Sidebar" class="d-block bg-sidebar-color-4 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor5Sidebar" value="sidebarcolorfive">
                                <label for="bgcolor5Sidebar" class="d-block bg-sidebar-color-5 mb-2">
                                </label>
                            </div>                            
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor6Sidebar" value="sidebarcolorsix">
                                <label for="bgcolor6Sidebar" class="d-block bg-sidebar-color-6 mb-2">
                                </label>
                            </div>                          
                            <div class="theme-colorselect mt-0 mb-2">
                                <div class="theme-container-background"></div>
                                <div class="pickr-container-background"></div>
                            </div>
                        </div>
                        <p class="mb-2 text-gray-9">Gradient Colors</p>
                        <div class="d-flex align-items-center">
                            <div class="theme-colorselect m-1 me-3">
                                <input type="radio" name="sidebar" id="bgcolor7Sidebar" value="sidebarcolorseven">
                                <label for="bgcolor7Sidebar" class="d-block bg-sidebar-color-7 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor8Sidebar" value="sidebarcoloreight">
                                <label for="bgcolor8Sidebar" class="d-block bg-sidebar-color-8 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor9Sidebar" value="sidebarcolornine">
                                <label for="bgcolor9Sidebar" class="d-block bg-sidebar-color-9 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor10Sidebar" value="sidebarcolorten">
                                <label for="bgcolor10Sidebar" class="d-block bg-sidebar-color-10 mb-2">
                                </label>
                            </div>
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor11Sidebar" value="sidebarcoloreleven">
                                <label for="bgcolor11Sidebar" class="d-block bg-sidebar-color-11 mb-2">
                                </label>
                            </div>  
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor12Sidebar" value="sidebarcolortwelve">
                                <label for="bgcolor12Sidebar" class="d-block bg-sidebar-color-12 mb-2">
                                </label>
                            </div>  
                            <div class="theme-colorselect me-3">
                                <input type="radio" name="sidebar" id="bgcolor13Sidebar" value="sidebarcolorthirteen">
                                <label for="bgcolor13Sidebar" class="d-block bg-sidebar-color-13 mb-2">
                                </label>
                            </div>  
                            <div class="theme-colorselect">
                                <input type="radio" name="sidebar" id="bgcolor14Sidebar" value="sidebarcolorfourteen">
                                <label for="bgcolor14Sidebar" class="d-block bg-sidebar-color-14 mb-2">
                                </label>
                            </div>  
                        </div>
                    </div>
                </div>
            </div>   
            <div class="accordion-item border px-3">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark fs-16 px-0 py-3 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#modesetting" aria-expanded="true">
                       Theme Mode
                    </button>
                </h2>
                <div id="modesetting" class="accordion-collapse collapse show">
                    <div class="accordion-body px-0 py3 border-top">
                       <div class="d-flex align-items-center">
                            <div class="theme-mode flex-fill text-center w-100 me-3">
                                <input type="radio" name="theme" id="lightTheme" value="light" checked>
                                <label for="lightTheme" class="rounded fw-medium w-100">                            
                                    <span class="d-inline-flex rounded me-2"><i class="ti ti-sun-filled"></i></span>Light
                                </label>
                            </div>
                            <div class="theme-mode flex-fill text-center w-100 me-3">
                                <input type="radio" name="theme" id="darkTheme" value="dark" >
                                <label for="darkTheme" class="rounded fw-medium w-100">                         
                                    <span class="d-inline-flex rounded me-2"><i class="ti ti-moon-filled"></i></span>Dark
                                </label>
                            </div>
                            <div class="theme-mode flex-fill w-100 text-center">
                                <input type="radio" name="theme" id="systemTheme" value="system">
                                <label for="systemTheme" class="rounded fw-medium w-100">                         
                                    <span class="d-inline-flex rounded me-2"><i class="ti ti-device-laptop"></i></span>System 
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    	    
            <div class="accordion-item border px-3 layout-select">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark fs-16 px-0 py-3 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarbgsetting" aria-expanded="true">
                        Sidebar Background
                    </button>
                </h2>
                <div id="sidebarbgsetting" class="accordion-collapse collapse show">
                    <div class="accordion-body pb-1 px-0 py-3 border-top">
                       <div class="d-flex align-items-center flex-wrap">
                            <div class="theme-sidebarbg me-3 mb-3">
                                <input type="radio" name="sidebarbg" id="sidebarBg1" value="sidebarbg1" checked>
                                <label for="sidebarBg1" class="d-block rounded">
                                    <img src="{{URL::asset('build/img/theme/sidebar-bg-01.svg')}}" alt="img" class="rounded">
                                </label>
                            </div>
                            <div class="theme-sidebarbg me-3 mb-3">
                                <input type="radio" name="sidebarbg" id="sidebarBg2" value="sidebarbg2">
                                <label for="sidebarBg2" class="d-block rounded">
                                    <img src="{{URL::asset('build/img/theme/sidebar-bg-02.svg')}}" alt="img" class="rounded">
                                </label>
                            </div>
                            <div class="theme-sidebarbg me-3 mb-3">
                                <input type="radio" name="sidebarbg" id="sidebarBg3" value="sidebarbg3">
                                <label for="sidebarBg3" class="d-block rounded">
                                    <img src="{{URL::asset('build/img/theme/sidebar-bg-03.svg')}}" alt="img" class="rounded">
                                </label>
                            </div>
                            <div class="theme-sidebarbg me-3 mb-3">
                                <input type="radio" name="sidebarbg" id="sidebarBg4" value="sidebarbg4">
                                <label for="sidebarBg4" class="d-block rounded">
                                    <img src="{{URL::asset('build/img/theme/sidebar-bg-04.svg')}}" alt="img" class="rounded">
                                </label>
                            </div>
                            <div class="theme-sidebarbg me-3 mb-3">
                                <input type="radio" name="sidebarbg" id="sidebarBg5" value="sidebarbg5">
                                <label for="sidebarBg5" class="d-block rounded">
                                    <img src="{{URL::asset('build/img/theme/sidebar-bg-05.svg')}}" alt="img" class="rounded">
                                </label>
                            </div>
                            <div class="theme-sidebarbg me-3 mb-3">
                                <input type="radio" name="sidebarbg" id="sidebarBg6" value="sidebarbg6">
                                <label for="sidebarBg6" class="d-block rounded">
                                    <img src="{{URL::asset('build/img/theme/sidebar-bg-06.svg')}}" alt="img" class="rounded">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    
            <div class="accordion-item border px-3">
                <h2 class="accordion-header">
                    <button class="accordion-button text-dark fs-16 px-0 py-3 bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarcolor" aria-expanded="true">
                        Theme Colors
                    </button>
                </h2>
                <div id="sidebarcolor" class="accordion-collapse collapse show"	 >
                    <div class="accordion-body pb-2 px-0 py-3 border-top">
                       <div class="d-flex align-items-center flex-wrap">
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="primaryColor" value="primary" checked>
                                <label for="primaryColor" class="primary-clr"></label>
                            </div>
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="brightblueColor" value="brightblue" >
                                <label for="brightblueColor" class="theme-color-1"></label>
                            </div>
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="lunargreenColor" value="lunargreen" >
                                <label for="lunargreenColor" class="theme-color-2"></label>
                            </div>
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="lavendarColor" value="lavendar">
                                <label for="lavendarColor" class="theme-color-3"></label>
                            </div>
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="magentaColor" value="magenta">
                                <label for="magentaColor" class="theme-color-4"></label>
                            </div>
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="chromeyellowColor" value="chromeyellow">
                                <label for="chromeyellowColor" class="theme-color-5"></label>
                            </div> 
                            <div class="theme-colorsset me-2 mb-2">
                                <input type="radio" name="color" id="orangeColor" value="orange">
                                <label for="orangeColor" class="theme-color-6"></label>
                            </div> 
                           <div class="theme-colorsset mb-2">                                
                                <div class="pickr-container-primary"  onchange="updateChartColor(this.value)"></div>
                                <div class="theme-container-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
    </div>
    <div class="p-3 pt-0">
        <div class="row gx-3">
            <div class="col-6">
                <a href="#" id="resetbutton" class="btn btn-light close-theme w-100"><i class="ti ti-restore me-1"></i>Reset</a>
            </div>
            <div class="col-6">
                <a href="#" class="btn btn-primary w-100" data-bs-dismiss="offcanvas"><i class="ti ti-shopping-cart-plus me-1"></i>Buy Product</a>
            </div>
        </div>
    </div>    
</div>
