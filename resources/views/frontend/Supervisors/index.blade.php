@extends('frontend.layouts.app'.config('theme_layout'))
@push('after-styles')
    <style>
        .couse-pagination li.active {
            color: #333333!important;
            font-weight: 700;
        }
        .page-link {
            position: relative;
            display: block;
            padding: .5rem .75rem;
            margin-left: -1px;
            line-height: 1.25;
            color: #c7c7c7;
            background-color: white;
            border: none;
        }
        .page-item.active .page-link {
            z-index: 1;
            color: #333333;
            background-color:white;
            border:none;

        }
        ul.pagination{
            display: inline;
            text-align: center;		
        }
    </style>
@endpush
@section('content')

	<!-- Start of breadcrumb section
		============================================= -->
		<section id="breadcrumb" class="breadcrumb-section relative-position backgroud-style">
			<div class="blakish-overlay"></div>
			<div class="container">
				<div class="page-breadcrumb-content text-center">
					<div class="page-breadcrumb-title">
						<h2 class="breadcrumb-head black bold">{{env('APP_NAME')}} <span>@lang('labels.frontend.Supervisor.title')</span></h2>
					</div>
				</div>
			</div>
		</section>
	<!-- End of breadcrumb section
		============================================= -->



	<!-- Start of Supervisor section
		============================================= -->
		<section id="Supervisor-page" class="Supervisor-page-section">
			<div class="container">
				<div class="row">
					<div class="col-md-9">
						<div class="Supervisors-archive">
							<div class="row">
                                @if(count($Supervisors) > 0)
                                @foreach($Supervisors as $item)
								<div class="col-md-4 col-sm-6">
									<div class="Supervisor-pic-content">
										<div class="Supervisor-img-content relative-position">
											<img src="{{$item->picture}}" alt="">
											<div class="Supervisor-hover-item">
												<div class="Supervisor-social-name ul-li-block">
													<ul>
                                                        <li><a href="#"><i class="fa fa-envelope"></i></a></li>
                                                        <li><a href="{{route('admin.messages',['Supervisor_id'=>$item->id])}}"><i class="fa fa-comments"></i></a></li>
													</ul>
												</div>
												{{--<div class="Supervisor-text">--}}
													{{--Lorem ipsum dolor  consectuer adipiscing elit, nonummy nibh euismod tincidunt.--}}
												{{--</div>--}}
											</div>
											<div class="Supervisor-next text-center">
												<a href="{{route('Supervisors.show',['id'=>$item->id])}}"><i class="text-gradiant fas fa-arrow-right"></i></a>
											</div>
										</div>
										<div class="Supervisor-name-designation">
											<span class="Supervisor-name">{{$item->full_name}}</span>
											{{--<span class="Supervisor-designation">Mobile Apps</span>--}}
										</div>
									</div>
								</div>
                                @endforeach
                                @else
                                    <h4>@lang('lables.general.no_data_available')</h4>
                                @endif


							</div>
							<div class="couse-pagination text-center ul-li">
                                {{ $Supervisors->links() }}
							</div>
							
						</div>
					</div>
					@include('frontend.layouts.partials.right-sidebar')
				</div>
			</div>
		</section>
	<!-- End of Supervisor section
		============================================= -->



@endsection