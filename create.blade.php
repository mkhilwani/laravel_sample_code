@extends('layouts.front')
@section('header')
@if(!Request::ajax())
@include('includes.frontheader')
@endif
@stop

@section('menu')
@if(!Request::ajax())
@include('includes.frontmenu')
@endif
@stop

@section('content')
@if(Session::has('success'))
<div class="alert alert-success">
    <strong>{{ Session::get('success')}}</strong>
</div>
@endif
<div class="col-lg-12 left-colon" id="addwinepost">
    <div class="tabbable custom-tabs tabs-animated  flat flat-all hide-label-980 shadow track-url auto-scroll">

        <!--            <ul class="nav nav-tabs">
                    <li class="active"><a class="active " data-toggle="tab" href="#step1"><i class="fa fa-arrow-circle-right"></i>&nbsp;<span>Profile</span></a></li>
                     <li class="disabled"><a data-toggle="tab" href="#step2"><i class="fa fa-arrow-circle-right"></i>&nbsp;<span>Preferences</span></a></li>
                     <li class="disabled"><a data-toggle="tab" href="#step3"><i class="fa fa-arrow-circle-right"></i>&nbsp;<span>Privacy</span></a></li>
              </ul>-->
        <ul class="nav nav-pills nav-justified thumbnail setup-panel">
            <li class="active">
                <a href="#step1" id="first-step">
                    <h4 class="list-group-item-heading">Step 1</h4>
                    <i class="fa fa-arrow-circle-right"></i>&nbsp;<span>Wine Detail  </span>
                </a>
            </li>
            <li class="disabled">
                <a href="#step2">
                    <h4 class="list-group-item-heading">Step 2</h4>
                    <i class="fa fa-arrow-circle-right"></i>&nbsp;<span>Preferences</span>
                </a>
            </li>
            <li class="disabled">
                <a href="#step3">
                    <h4 class="list-group-item-heading">Step 3</h4>
                    <i class="fa fa-arrow-circle-right"></i>&nbsp;<span>Privacy</span>
                </a>
            </li>
        </ul>


        <div class="tab-content fontsize_15">
            <div id="step1" class="tab-pane setup-content">
                <div class="row">

                    <div class="col-lg-12">
                        {{ Form::open(array('url' =>
                        'winepost/addnewwinepost','name'=>'add_wine_post','id'=>'add_wine_post')) }}
                        <div class="form-group">
                            Public {{ Form::radio('is_public','1',true) }} &nbsp;&nbsp;
                            Private {{ Form::radio('is_public','0') }}
                            <div class="input-error"></div>
                        </div>
                        {{ Form::hidden('HiddenWpId', '0', array('id'=>'HiddenWpId')) }}
                        {{ Form::hidden('winepostid', '0', array('id'=>'winepostid')) }}
                        <div class="form-group">
                            {{ Form::label('title ', 'Title *')}}
                            {{ Form::text('title', null, array('placeholder' => 'Winepost
                            Title','required'=>'required','id'=>'title','class'=>'form-control')) }}
                            <div class="input-error"></div>
                        </div>

                        <div class="form-group">
                            {{ Form::label('url ', 'Permanent URL:')}}
                            A URL will be automatically generated using your nickname and title
                            <i id='tooltip' class="fa fa-info-circle"
                               data-toggle="tooltip"
                               data-placement="top"
                               title=""
                               data-original-title="A flawed bottle may be affected by cork taint, heat (maderized), oxidation, or excessive sulfur. Additionally, some people consider wines tainted by Brettanomyces (Brett), a non-spore forming genus of yeast, a flaw although there is much controversy surrounding the subject."></i>

                            <div class="input-error"></div>
                        </div>
                        <div class="form-group">
                            {{ Form::label('description', 'Description*')}}
                            {{ Form::textarea('description',null,array('placeholder' =>
                            'description','id'=>'description','class'=>'ckeditor')) }}
                            <div class="input-error"></div>
                        </div>

                        <div class="form-group">
                            {{ Form::label('winepost_tags', 'Winepost Tags (ex:Nice,good)')}}
                            {{ Form::text('winepost_tags',null, array('placeholder' => 'Winepost
                            Tags','id'=>'winepost_tags','class'=>'form-control')) }}
                            <div class="input-error"></div>
                        </div>

                        <div class="form-group">
                            {{ Form::label('category_id', 'Select Category *')}}
                            {{Form::select('category_id', $category,null,array('class'=>'form-control')); }}
                            <div class="input-error"></div>
                        </div>

                        <div class="form-group">
                            {{ Form::label('image_name', 'Wine Post image')}}

                            <div id="queue"></div>
                            <input id="file_upload_front" name="file_upload_front" accept="image/*" type="file"
                                   multiple="true">

                            <div id="progress"></div>
                            <div class="input-error"></div>
                        </div>
                        <!-- hidden field for add video functionality -->
                        {{ Form::hidden('hiddencount',1, array('id'=>'hiddencount')) }}
                        {{ Form::hidden('uploadUrl',$uploadUrl,array('id'=>'uploadUrl'))}}
                        {{ Form::hidden('hiddenfileuploads',null,array('id'=>'hiddenfileuploads'))}}
                        <div class="form-group" id='InputsWrapper'>
                            <div id='id_1'>
                                {{ Form::text('videourl[]', null, array('placeholder' => 'Video Url
                                ','style'=>'margin-top:6px;','id'=>'videourl_1','class'=>'form-control youtube_video'))
                                }}
                                {{ Form::text('videocaption[]', null, array('placeholder' => 'Video Caption
                                ','id'=>'videocaption_1','style'=>'margin-top:10px;','class'=>'form-control')) }}
                            </div>
                        </div>
                              
                             <span class="small">
                            <a id="AddMoreFileBoxfront" class="btn btn-color-blue" href="#">Add Video</a>
                            </span>

                        <br/><br/>

                        <div class="fltleft" style="margin-left: 20%;"></div>
                        <button class="btn btn-primary-red btn-lg right" id='next' type="button">Save and Next</button>
                        <!--                            <button class="btn btn-primary btn-lg" type="reset">Reset</button>-->
                        {{-- link_to('portaladmin/winepost','Cancel',array('class'=>'btn btn-primary-red btn-lg',
                        'style'=>'text-decoration:none;')) --}}

                        {{ Form::close() }}
                    </div>
                </div>
            </div>


            <div id="step2" class="tab-pane winepost-image-step setup-content">
                <div class="spacer15"></div>
                <div class="row">
                    <div class="col-xs-14 col-md-12 pull-left left_top">
                        <h4>To add wine, enter a year and a keyword. For non-vintage wine enter NV. </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-xs-4">
                        <div class="input-group">
                            <span class="input-group-addon">Vintage</span>
                            <input type="text" id="txt-search-vintage" maxlength="4" name="txt-search-vintage"
                                   class="form-control">
                        </div>
                    </div>

                    <div class="col-md-5 col-xs-8">
                        <div class="input-group">
                            <span class="input-group-addon">Search Wine</span>
                            <input type="text" id="txt-search-wine" maxlength="100" name="txt-search-wine"
                                   class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3 col-xs-4 pull-left">
                        <div class="input-group">
                            <button id="btn-search-wine" type="button" class="btn btn-danger"><i
                                    class="fa fa-search-plus"></i>&nbsp;Search
                            </button>
                            <button id="btn-next-step" type="button" class="btn btn-danger disabled"><i
                                    class="fa fa-search-plus"></i>&nbsp;Next
                            </button>

                        </div>

                    </div>
                </div>

                <div id="errmsg"></div>
                <div class="spacer15"></div>

                <div id="ajaxresults"></div>

            </div>


            <div id="step3" class="tab-pane setup-content">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class='padding-bottom-20'>
                            <button id="btn-back-selected-list-step" type="button" class="btn btn-danger left"><i
                                    class="fa fa-search-plus"></i>&nbsp;Add Wine to WinePost
                            </button>
                            <strong class='margin_left_20'>Privacy</strong>
                            <button id="form_submit_check" type="button" class="btn btn-danger right"><i
                                    class="fa fa-search-plus"></i>&nbsp;Save and Post
                            </button>
                        </h4>
                        <div class='clear'></div>

                        <div class="alert alert-danger hidden" id="err-msg">
                            <button class="close" id="homes" aria-hidden="true" data-dismiss="alert" type="button">×
                            </button>
                        </div>

                        <div class='clear_fix'></div>
                        <div id="selectedproductslistId"></div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
<div class="clear_fix"></div>
<!-- My
</div>  
<!-- end here -->

<!-- manual entry add blade start here-->
@include('includes.manualwineentry')
<!-- manual entry end here -->

@stop
@section('footer')
@if(!Request::ajax())
@include('includes.frontfooter')
@else
@include('includes.frontscriptfooter')
@endif
@stop