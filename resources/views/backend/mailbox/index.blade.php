@extends('backend.layouts.app')
@section('title', __('MailBox').' | '.app_name())
@push('after-styles')
<style type="text/css">
    .mail-box {
        border-collapse: collapse;
        border-spacing: 0;
        display: table;
        table-layout: fixed;
        width: 100%;
    }
    .mail-box aside {
        display: table-cell;
        float: none;
        height: 100%;
        padding: 0;
        vertical-align: top;
    }
    .mail-box .sm-side {
        background: none repeat scroll 0 0 #e5e8ef;
        border-radius: 4px 0 0 4px;
        width: 25%;
    }
    .mail-box .lg-side {
        background: none repeat scroll 0 0 #fff;
        border-radius: 0 4px 4px 0;
        width: 75%;
    }
    .mail-box .sm-side .user-head {
        background: none repeat scroll 0 0 #00a8b3;
        color: #fff;
        min-height: 80px;
        padding: 10px;
    }
    .user-head .inbox-avatar {
        float: left;
        width: 65px;
    }
    .user-head .inbox-avatar img {
        border-radius: 4px;
    }
    .user-head .user-name {
        display: inline-block;
        margin: 0 0 0 10px;
    }
    .user-head .user-name h5 {
        font-size: 14px;
        font-weight: 300;
        margin-bottom: 0;
        margin-top: 15px;
    }
    .user-head .user-name h5 a {
        color: #fff;
    }
    .user-head .user-name span a {
        color: #87e2e7;
        font-size: 12px;
    }
    a.mail-dropdown {
        background: none repeat scroll 0 0 #80d3d9;
        border-radius: 2px;
        color: #01a7b3;
        font-size: 10px;
        margin-top: 20px;
        padding: 3px 5px;
    }
    .inbox-body {
        padding: 20px;
    }
    .btn-compose {
        color: #fff;
        padding: 12px 0;
        text-align: center;
        width: 100%;
    }
    .btn-compose:hover {
        color: #fff;
    }
    ul.inbox-nav {
        display: inline-block;
        margin: 0;
        padding: 0;
        width: 100%;
    }
    .inbox-divider {
        border-bottom: 1px solid #d5d8df;
    }
    ul.inbox-nav li {
        display: inline-block;
        line-height: 45px;
        width: 100%;
    }
    ul.inbox-nav li a {
        color: #6a6a6a;
        display: inline-block;
        line-height: 45px;
        padding: 0 20px;
        width: 100%;
    }
    ul.inbox-nav li a:hover, ul.inbox-nav li.active a, ul.inbox-nav li a:focus {
        background: none repeat scroll 0 0 #d5d7de;
        color: #6a6a6a;
    }
    ul.inbox-nav li a i {
        color: #6a6a6a;
        font-size: 16px;
        padding-right: 10px;
    }
    ul.inbox-nav li a span.label {
        margin-top: 0px;
    }
    ul.labels-info li h4 {
        color: #5c5c5e;
        font-size: 13px;
        padding-left: 15px;
        padding-right: 15px;
        padding-top: 5px;
        text-transform: uppercase;
    }
    ul.labels-info li {
        margin: 0;
    }
    ul.labels-info li a {
        border-radius: 0;
        color: #6a6a6a;
    }
    ul.labels-info li a:hover, ul.labels-info li a:focus {
        background: none repeat scroll 0 0 #d5d7de;
        color: #6a6a6a;
    }
    ul.labels-info li a i {
        padding-right: 10px;
    }
    .nav.nav-pills.nav-stacked.labels-info p {
        color: #9d9f9e;
        font-size: 11px;
        margin-bottom: 0;
        padding: 0 22px;
    }
    .inbox-head {
        background: none repeat scroll 0 0 #41cac0;
        color: #fff;
        min-height: 80px;
        padding: 20px;
    }
    .inbox-head h3 {
        display: inline-block;
        font-weight: 300;
        margin: 0;
        padding-top: 6px;
    }
    .inbox-head .sr-input {
        border: medium none;
        border-radius: 4px 0 0 4px;
        box-shadow: none;
        color: #8a8a8a;
        float: left;
        height: 40px;
        padding: 0 10px;
    }
    .inbox-head .sr-btn {
        background: none repeat scroll 0 0 #00a6b2;
        border: medium none;
        border-radius: 0 4px 4px 0;
        color: #fff;
        height: 40px;
        padding: 0 20px;
    }
    .table-inbox {
        border: 1px solid #d3d3d3;
        margin-bottom: 0;
    }
    .table-inbox tr td {
        padding: 12px !important;
    }
    .table-inbox tr td:hover {
        cursor: pointer;
    }
    .table-inbox tr td .fa-star.inbox-started, .table-inbox tr td .fa-star:hover {
        color: #f78a09;
    }
    .table-inbox tr td .fa-star {
        color: #d5d5d5;
    }
    .table-inbox tr.unread td {
        background: none repeat scroll 0 0 #f7f7f7;
        font-weight: 600;
    }
    ul.inbox-pagination {
        float: right;
    }
    ul.inbox-pagination li {
        float: left;
    }
    .mail-option {
        display: inline-block;
        margin-bottom: 10px;
        width: 100%;
    }
    .mail-option .chk-all, .mail-option .btn-group {
        margin-right: 5px;
    }
    .mail-option .chk-all, .mail-option .btn-group a.btn {
        background: none repeat scroll 0 0 #fcfcfc;
        border: 1px solid #e7e7e7;
        border-radius: 3px !important;
        color: #afafaf;
        display: inline-block;
        padding: 5px 10px;
    }
    .inbox-pagination a.np-btn {
        background: none repeat scroll 0 0 #fcfcfc;
        border: 1px solid #e7e7e7;
        border-radius: 3px !important;
        color: #afafaf;
        display: inline-block;
        padding: 5px 15px;
    }
    .mail-option .chk-all input[type="checkbox"] {
        margin-top: 0;
    }
    .mail-option .btn-group a.all {
        border: medium none;
        padding: 0;
    }
    .inbox-pagination a.np-btn {
        margin-left: 5px;
    }
    .inbox-pagination li span {
        display: inline-block;
        margin-right: 5px;
        margin-top: 7px;
    }
    .fileinput-button {
        background: none repeat scroll 0 0 #eeeeee;
        border: 1px solid #e6e6e6;
    }
    .inbox-body .modal .modal-body input, .inbox-body .modal .modal-body textarea {
        border: 1px solid #e6e6e6;
        box-shadow: none;
    }
    .btn-send, .btn-send:hover {
        background: none repeat scroll 0 0 #00a8b3;
        color: #fff;
    }
    .btn-send:hover {
        background: none repeat scroll 0 0 #009da7;
    }
    .modal-header h4.modal-title {
        font-family: "Open Sans",sans-serif;
        font-weight: 300;
    }
    .modal-body label {
        font-family: "Open Sans",sans-serif;
        font-weight: 400;
    }
    .heading-inbox h4 {
        border-bottom: 1px solid #ddd;
        color: #444;
        font-size: 18px;
        margin-top: 20px;
        padding-bottom: 10px;
    }
    .sender-info {
        margin-bottom: 20px;
    }
    .sender-info img {
        height: 30px;
        width: 30px;
    }
    .sender-dropdown {
        background: none repeat scroll 0 0 #eaeaea;
        color: #777;
        font-size: 10px;
        padding: 0 3px;
    }
    .view-mail a {
        color: #ff6c60;
    }
    .attachment-mail {
        margin-top: 30px;
    }
    .attachment-mail ul {
        display: inline-block;
        margin-bottom: 30px;
        width: 100%;
    }
    .attachment-mail ul li {
        float: left;
        margin-bottom: 10px;
        margin-right: 10px;
        width: 150px;
    }
    .attachment-mail ul li img {
        width: 100%;
    }
    .attachment-mail ul li span {
        float: right;
    }
    .attachment-mail .file-name {
        float: left;
    }
    .attachment-mail .links {
        display: inline-block;
        width: 100%;
    }

    .fileinput-button {
        float: left;
        margin-right: 4px;
        overflow: hidden;
        position: relative;
    }
    .fileinput-button input {
        cursor: pointer;
        direction: ltr;
        font-size: 23px;
        margin: 0;
        opacity: 0;
        position: absolute;
        right: 0;
        top: 0;
        transform: translate(-300px, 0px) scale(4);
    }
    .fileupload-buttonbar .btn, .fileupload-buttonbar .toggle {
        margin-bottom: 5px;
    }
    .files .progress {
        width: 200px;
    }
    .fileupload-processing .fileupload-loading {
        display: block;
    }
    * html .fileinput-button {
        line-height: 24px;
        margin: 1px -3px 0 0;
    }
    * + html .fileinput-button {
        margin: 1px 0 0;
        padding: 2px 15px;
    }
    @media (max-width: 767px) {
    .files .btn span {
        display: none;
    }
    .files .preview * {
        width: 40px;
    }
    .files .name * {
        display: inline-block;
        width: 80px;
        word-wrap: break-word;
    }
    .files .progress {
        width: 20px;
    }
    .files .delete {
        width: 60px;
    }
    }
    ul {
        list-style-type: none;
        padding: 0px;
        margin: 0px;
    }
    .card-body {
        padding: 0px !important;
        margin: 0px !important;
    }
    .note-editable.card-block{
        min-height: 250px;
    }
    .note-editable.card-block p{margin: 0px !important;}
    .btn-send{display: block;margin: auto;width: 40%;}
</style>
<link rel="stylesheet" href="{{ url('public/css/summernote-bs4.css') }}">
<link rel="stylesheet" href="https://www.dropzonejs.com/css/dropzone.css">
@endpush
@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="page-title d-inline">@lang('MailBox')</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                  <div class="mail-box">
                    <aside class="sm-side">
                      <div class="user-head">
                        <a class="inbox-avatar" href="javascript:;">
                          <img width="64" hieght="60" src="https://bootsnipp.com/img/avatars/ebeb306fd7ec11ab68cbcaa34282158bd80361a7.jpg">
                        </a>
                        <div class="user-name">
                          <h5><a href="#">Alireza Zare</a></h5>
                          <span><a href="#">Info.Ali.Pci@Gmail.com</a></span>
                        </div>
                        <a class="mail-dropdown pull-right" href="javascript:;">
                          <i class="fa fa-chevron-down"></i>
                        </a>
                      </div>
                      <div class="inbox-body">
                        <a href="#myModal" data-toggle="modal" title="Compose" class="btn btn-compose btn-primary">Compose</a>
                        <!-- Modal -->
                        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade" style="display: none;">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title">Compose</h4>
                                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                              </div>
                              <div class="modal-body">
                                <form role="form" class="form-horizontal" id="composeForm">
                                  <div class="form-group">
                                    <label class="control-label">To</label>
                                    <input type="email" placeholder="To" name="to" id="inputEmail1" class="form-control">
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label">Cc / Bcc</label>
                                    <input type="text" placeholder="Cc/Bcc" name="cc" id="cc" class="form-control">
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label">Subject</label>
                                    <input type="text" name="subject" placeholder="Subject" id="subject" class="form-control" required>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label">Message</label>
                                    <textarea rows="10" cols="30" class="form-control summernote" id="" name="message" required placeholder="Enter your message"></textarea>
                                  </div>
                                  <div class="form-group">
                                    <div class="dropzone" id="myDropzone">
                                        <div class="dz-message" data-dz-message><span>Drag or Upload Attachment</span></div>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <button class="btn btn-send btn-primary" type="submit">Send</button>
                                  </div>
                                </form>
                              </div>
                            </div>
                            <!-- /.modal-content -->
                          </div>
                          <!-- /.modal-dialog -->
                        </div>
                        <!-- /.modal -->
                      </div>
                      <ul class="inbox-nav inbox-divider">
                        <li class="active">
                          <a href="#"><i class="fa fa-inbox"></i> Inbox <span class="label label-danger pull-right">2</span></a>

                        </li>
                        <li>
                          <a href="#"><i class="fa fa-envelope-o"></i> Sent Mail</a>
                        </li>
                        <li>
                          <a href="#"><i class="fa fa-bookmark-o"></i> Important</a>
                        </li>
                        <li>
                          <a href="#"><i class=" fa fa-external-link"></i> Drafts <span class="label label-info pull-right">30</span></a>
                        </li>
                        <li>
                          <a href="#"><i class=" fa fa-trash-o"></i> Trash</a>
                        </li>
                      </ul>
                      <div class="inbox-body text-center">
                        <div class="btn-group">
                          <a class="btn mini btn-primary" href="javascript:;">
                            <i class="fa fa-plus"></i>
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn mini btn-success" href="javascript:;">
                            <i class="fa fa-phone"></i>
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn mini btn-info" href="javascript:;">
                            <i class="fa fa-cog"></i>
                          </a>
                        </div>
                      </div>

                    </aside>
                    <aside class="lg-side">
                      <div class="inbox-head">
                        <h3>Inbox</h3>
                        <form action="#" class="pull-right position">
                          <div class="input-append">
                            <input type="text" class="sr-input" placeholder="Search Mail">
                            <button class="btn sr-btn" type="button"><i class="fa fa-search"></i></button>
                          </div>
                        </form>
                      </div>
                      <div class="inbox-body">
                        <div class="mail-option">
                          <div class="chk-all">
                            <input type="checkbox" class="mail-checkbox mail-group-checkbox">
                            <div class="btn-group">
                              <a data-toggle="dropdown" href="#" class="btn mini all" aria-expanded="false">
                                                         All
                                                         <i class="fa fa-angle-down "></i>
                                                     </a>
                              <ul class="dropdown-menu">
                                <li><a href="#"> None</a></li>
                                <li><a href="#"> Read</a></li>
                                <li><a href="#"> Unread</a></li>
                              </ul>
                            </div>
                          </div>

                          <div class="btn-group">
                            <a data-original-title="Refresh" data-placement="top" data-toggle="dropdown" href="#" class="btn mini tooltips">
                              <i class=" fa fa-refresh"></i>
                            </a>
                          </div>
                          <div class="btn-group hidden-phone">
                            <a data-toggle="dropdown" href="#" class="btn mini blue" aria-expanded="false">
                                                     More
                                                     <i class="fa fa-angle-down "></i>
                                                 </a>
                            <ul class="dropdown-menu">
                              <li><a href="#"><i class="fa fa-pencil"></i> Mark as Read</a></li>
                              <li><a href="#"><i class="fa fa-ban"></i> Spam</a></li>
                              <li class="divider"></li>
                              <li><a href="#"><i class="fa fa-trash-o"></i> Delete</a></li>
                            </ul>
                          </div>
                          <div class="btn-group">
                            <a data-toggle="dropdown" href="#" class="btn mini blue">
                                                     Move to
                                                     <i class="fa fa-angle-down "></i>
                                                 </a>
                            <ul class="dropdown-menu">
                              <li><a href="#"><i class="fa fa-pencil"></i> Mark as Read</a></li>
                              <li><a href="#"><i class="fa fa-ban"></i> Spam</a></li>
                              <li class="divider"></li>
                              <li><a href="#"><i class="fa fa-trash-o"></i> Delete</a></li>
                            </ul>
                          </div>

                          <ul class="unstyled inbox-pagination">
                            <li><span>1-50 of 234</span></li>
                            <li>
                              <a class="np-btn" href="#"><i class="fa fa-angle-left  pagination-left"></i></a>
                            </li>
                            <li>
                              <a class="np-btn" href="#"><i class="fa fa-angle-right pagination-right"></i></a>
                            </li>
                          </ul>
                        </div>
                        <table class="table table-inbox table-hover">
                          <tbody>
                            <tr class="unread">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message  dont-show">PHPClass</td>
                              <td class="view-message ">Added a new class: Login Class Fast Site</td>
                              <td class="view-message  inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message  text-right">9:27 AM</td>
                            </tr>
                            <tr class="unread">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Google Webmaster </td>
                              <td class="view-message">Improve the search presence of WebSite</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">March 15</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">JW Player</td>
                              <td class="view-message">Last Chance: Upgrade to Pro for </td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">March 15</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Tim Reid, S P N</td>
                              <td class="view-message">Boost Your Website Traffic</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">April 01</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="view-message dont-show">Freelancer.com <span class="label label-danger pull-right">urgent</span></td>
                              <td class="view-message">Stop wasting your visitors </td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">May 23</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="view-message dont-show">WOW Slider </td>
                              <td class="view-message">New WOW Slider v7.8 - 67% off</td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">March 14</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="view-message dont-show">LinkedIn Pulse</td>
                              <td class="view-message">The One Sign Your Co-Worker Will Stab</td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">Feb 19</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Drupal Community<span class="label label-success pull-right">megazine</span></td>
                              <td class="view-message view-message">Welcome to the Drupal Community</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">March 04</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Facebook</td>
                              <td class="view-message view-message">Somebody requested a new password </td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">June 13</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Skype <span class="label label-info pull-right">family</span></td>
                              <td class="view-message view-message">Password successfully changed</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">March 24</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="view-message dont-show">Google+</td>
                              <td class="view-message">alireza, do you know</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">March 09</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="dont-show">Zoosk </td>
                              <td class="view-message">7 new singles we think you'll like</td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">May 14</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">LinkedIn </td>
                              <td class="view-message">Alireza: Nokia Networks, System Group and </td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">February 25</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="dont-show">Facebook</td>
                              <td class="view-message view-message">Your account was recently logged into</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">March 14</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Twitter</td>
                              <td class="view-message">Your Twitter password has been changed</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">April 07</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">InternetSeer Website Monitoring</td>
                              <td class="view-message">http://golddesigner.org/ Performance Report</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">July 14</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="view-message dont-show">AddMe.com</td>
                              <td class="view-message">Submit Your Website to the AddMe Business Directory</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">August 10</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Terri Rexer, S P N</td>
                              <td class="view-message view-message">Forget Google AdWords: Un-Limited Clicks fo</td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">April 14</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Bertina </td>
                              <td class="view-message">IMPORTANT: Don't lose your domains!</td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">June 16</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star inbox-started"></i></td>
                              <td class="view-message dont-show">Laura Gaffin, S P N </td>
                              <td class="view-message">Your Website On Google (Higher Rankings Are Better)</td>
                              <td class="view-message inbox-small-cells"></td>
                              <td class="view-message text-right">August 10</td>
                            </tr>
                            <tr class="">
                              <td class="inbox-small-cells">
                                <input type="checkbox" class="mail-checkbox">
                              </td>
                              <td class="inbox-small-cells"><i class="fa fa-star"></i></td>
                              <td class="view-message dont-show">Facebook</td>
                              <td class="view-message view-message">Alireza Zare Login faild</td>
                              <td class="view-message inbox-small-cells"><i class="fa fa-paperclip"></i></td>
                              <td class="view-message text-right">feb 14</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </aside>
                  </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script type="text/javascript" src="{{ url('public/js/summernote-bs4.js') }}"></script>
<script type="text/javascript" src="https://www.dropzonejs.com/js/dropzone.js"></script>
    <script>
        $(document).ready(function () {
            $('.summernote').summernote({});
        });
        Dropzone.options.myDropzone= {
                url: '{{ route("admin.mailbox") }}',
                autoDiscover: false,
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 50,
                maxFilesize: 50,
                addRemoveLinks: true,
                sending: function(file, xhr, formData) {
                    formData.append("_token", "{{ csrf_token() }}");
                    formData.append("to", jQuery("#to").val());
                    formData.append("cc", jQuery("#cc").val());
                    formData.append("subject", jQuery("#subject").val());
                    formData.append("message", jQuery("#message").val());
            },
                init: function() {
                    dzClosure = this;
                    $("#composeForm").on("submit", function(e) {
                        $("#composeForm .btn-send").html('<i class="fa fa fa-spinner fa-spin"></i> Processing...');
                        $("#composeForm .btn-send").prop('disabled', true);
                        if (dzClosure.getQueuedFiles().length > 0) {
                            e.preventDefault();
                            e.stopPropagation();
                            for (var i=0; i<dzClosure.getQueuedFiles().length; i++) {
                                // console.log(dzClosure.files[i].name);
                            }
                            dzClosure.processQueue();                        
                        }  
                    });
                },
                success: function(file, response)
                {
                    console.log(response);
                    alert('Sent');
                },
                error: function(file, response)
                {
                    console.log(response);
                    alert('error');
                }
            }
    </script>
@endpush