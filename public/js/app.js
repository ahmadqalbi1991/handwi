var App = (function ($) {
  "use strict";

  var defaults = {
      'site_url': '',
      'base_url': '',
      'site_name': '',
  }
  var config = {};

  var appInit = function ( options ) {
      config = $.extend({}, defaults, options);
  }

  var siteUrl = function ( uri ) {
      uri = uri || '';
      return config.site_url + uri;
  }

  var baseUrl = function ( uri ) {
      uri = uri || '';
      return config.base_url + uri;
  }

  var siteName = function () {
      return config.site_name;
  }

  var handleUI = function () {

      // Handle sidebar visibility
      $('body').off('click', '[data-toggle="minimize"]');
      $('body').on('click', '[data-toggle="minimize"]', function(e) {
          e.preventDefault();
          if ( $('body').hasClass('sidebar-icon-only') ) {
              document.cookie = "sidebar_icon_only=1; expires=Fri, 01 Jan 2038 12:00:00 UTC; path=/";
          } else {
              document.cookie = "sidebar_icon_only=0; expires=Fri, 01 Jan 2038 12:00:00 UTC; path=/";
          }
      });

      // Handle record delete
    //   $('body').off('click', '[data-role="unlink"]');
    //   $('body').on('click', '[data-role="unlink"]', function(e) { 
    //       e.preventDefault();
    //       var msg = $(this).data('message')||'Are you sure that you want to delete this record?';
    //       var href = $(this).attr('href');

    //       showConfirmModal('Confirm Delete', msg, function() {
    //           $.post(href, function(res) {
    //               if ( res['status'] == 1 ) {
    //                   showAlertModal(res['message']||'Deleted successfully', 'Success!');
    //                   setTimeout(function(){
    //                       window.location.reload();
    //                   },1500);
                      
    //               } else {
    //                   showAlertModal(res['message']||'Unable to delete the record.', 'Failed!');
    //               }
    //           });
    //       });
    //   });

      // Handle Active/Inactive record
      $('body').off('change', '[data-role="active-switch"]');
      $('body').on('change', '[data-role="active-switch"]', function(e) {
          var $this = $(this);
          var status = $this.is(':checked') == 1 ? 1 : 0;

          $.post($this.data('href'), {
              'status': status
          }, function(res) {
              if ( res['status'] == 1 ) {

              } else {
                  showToast([[ res['message'], 'error' ]]);
              }
          });
      });

      // handle sort ordering

      $('body').off('change', '.ordering]');
      $('body').on('change', '.ordering', function(e) {
          var $this   = $(this);
          var order   = $this.val();
          var id      = $this.data('id');

          $.post($this.data('href'), {
              'order': order,'id':id
          }, function(res) {
              if ( res['status'] == 1 ) 
              {
                window.location.reload();  
              } 
              else {
                  showToast([[ res['message'], 'error' ]]);
              }
          });
      });

      $('body').find('[data-role="view-password"]').siblings('[type="password"]').addClass('x-input-password');

      $('body').off('click', '[data-role="view-password"]');
      $('body').on('click', '[data-role="view-password"]', function(e) {
          e.preventDefault();
          var $t = $(this);
          var $password = $t.siblings('.x-input-password');
          if ( $password.prop('type') == 'password' ) {
              $password.prop('type', 'text');
              $t.find('i').removeClass('fa-eye').addClass('fa-eye-slash');
          } else {
              $password.prop('type', 'password');
              $t.find('i').removeClass('fa-eye-slash').addClass('fa-eye');
          }
      });

      // Select on Focus
      $('body').off('click', 'input.select-on-focus');
      $('body').on('click', 'input.select-on-focus', function(e) {
          e.preventDefault();
          $(this).select();
      });
  }

  var handleUISearch = function() {
      $('body').off('click', '.search-view-more');
      $('body').on('click', '.search-view-more', function(e) {
          e.preventDefault();
          var $this = $(this);
          var $parent = $this.closest('.ui-search-view');
          var $filter = $parent.find('.ui-search-filter');

          $filter.toggleClass('d-none');
          if ( $filter.hasClass('d-none') ) {
              $this.find('i').removeClass('fa-search-minus').addClass('fa-search-plus');
          } else {
              $this.find('i').removeClass('fa-search-plus').addClass('fa-search-minus');
          }
      });

      $('body').off('change', '.ui-search-view [name="search_name"]');
      $('body').on('change', '.ui-search-view [name="search_name"]', function() {
          $(this).closest('form').trigger('submit');
      });

      $('body').find('.ui-search-view input[type="text"]').attr('autocomplete','off');

      var downloading = 0;
      $('body').off('click', '[data-role="ui-search-download"]');
      $('body').on('click', '[data-role="ui-search-download"]', function(e) {
          e.preventDefault();
          var $t = $(this);
          var $searchForm = $t.closest('form');

          if ( downloading ) return;

          downloading = 1;
          App.loading(true);
          $t.attr('disabled', true);

          var formData = $searchForm.serializeArray();
          formData['search_name'] = $searchForm.find('.ui-search-input').val();

          $.post($t.data('href'), formData, function(res) {                
              downloading = 0;
              App.loading(false);
              $t.attr('disabled', false);

              if ( res['status'] == 1 && typeof res['token'] !== 'undefined' ) {
                  window.location.href = $t.data('href')+'/'+res['token'];
              } else {
                  var m = res['message']||'Oops! unable to download.';
                  App.toast([[m, 'error']]);
              }
          });

      });
  }

  var handleTreeView = function() {
      $('[data-role="ui-datatable"]').each(function() {
          _bindDatatable( $(this) );
      });
      $('[data-role="ui-datatable-static"]').each(function() {
          $(this).DataTable({
              'searching': false,
              'processing': true,
              'serverSide': false,
              'stateSave': false,
              'autoWidth': false,
              'aLengthMenu': [
                  [10, 25, 50, 100],
                  [10, 25, 50, 100]
              ],
              'iDisplayLength': 10,                
              'pagingType': 'simple',
              'language': {
                  'paginate': {
                      'next': '<i class="mdi mdi-arrow-right mdi-24px"></i>',
                      'previous': '<i class="mdi mdi-arrow-left mdi-24px"></i>'
                  }
              },
              'fnDrawCallback': function(oSettings) {
                  if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                      $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                  } else {
                      $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
                  }
              }
          });
      });

      handleUISearch();
  }

  var _bindDatatable = function( $table ) {
      var columns = [];
      var order = [];
      var length = $table.find('thead th').length;
      var $searchForm = $('body').find('.ui-search-view form');
      var $dataTable;
      var def = $.Deferred();

      $table.find('thead th').each(function(index) {
          var _cname  = $(this).data('colname');
          var _cwidth = $(this).data('colwidth');
          var _cidx = parseInt(index);
          if ( typeof _cname == 'undefined' || _cname == '' ) {
              showAlertModal('Datatable configuration error: missing colname for '+ (_cidx+1), 'Error');
              def.reject();
              return;
          } else {
              var _c = {'data': _cname};
              if ( typeof _cwidth !== 'undefined' ) {
                  _c['width'] = _cwidth;
              }
              columns.push(_c);
          }
          if ( length == (_cidx+1)) {
              def.resolve();
          }
      });

      if ( typeof $table.data('table-order') !== 'undefined' ) {
          var order = eval($table.data('table-order'));
          if(order == false){
              order = '';
          }
      } else {
          order = [[ 0, 'desc' ]];
      }

      def.done(function() {

          $dataTable = $table.dataTable( {
              'searching': false,
              'processing': true,
              'serverSide': true,
              'stateSave': false,
              'autoWidth': false,
              'ajax': {
                  'url': $table.data('src'),
                  'type': 'POST',
                  'data': function(data) {
                      data['search_name'] = $searchForm.find('.ui-search-input').val();
                      var inputs = $("#filter_input :input");
                      $(inputs).each(function() {
                          data[$(this).attr("name")] = $(this).val();
                      });
                      data['sort'] = $("#sort a.selected").attr("data-id");
                  },
              },
              'aLengthMenu': [
                  [10, 25, 50, 100],
                  [10, 25, 50, 100]
              ],
              'iDisplayLength': 10,
              'language': {
                  search: ''
              },
              'order': order,
              'columns': columns,
              'pagingType': 'simple',
              'language': {
                  'paginate': {
                      'next': '<i class="mdi mdi-arrow-right mdi-24px"></i>',
                      'previous': '<i class="mdi mdi-arrow-left mdi-24px"></i>'
                  }
              },
              'rowCallback' : function (nRow, aData, iDisplayIndex) {
                  var oSettings = this.fnSettings ();
                  $("td:first", nRow).html(oSettings._iDisplayStart+iDisplayIndex +1);
                  return nRow;
              },
              'fnDrawCallback': function(oSettings) {
                  if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                      $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                  } else {
                      $(oSettings.nTableWrapper).find('.dataTables_paginate').show();
                  }
              }
          });
      });

      $('body').off('change', '.ui-search-view form');
      $('body').on('submit', '.ui-search-view form', function(e) {
          e.preventDefault();
          $dataTable.fnDraw();
      });
      $("#filter-here").click(function(){
          $dataTable.fnDraw();
          
      });
      $("#sort a").click(function(e){
          e.preventDefault();
          var sort = $(this).attr("data-id");
          var text = $(this).text();
          $("#sort a").removeClass("selected");
          $(this).addClass("selected");
          $("#sort-btn").text(text);
          $dataTable.fnDraw();
      });

  }

  var handleFormView = function() {

      // Load Provinces on Country Change
      $('body').off('change', '[data-trigger="country-change"]');
      $('body').on('change', '[data-trigger="country-change"]', function() {
          var $t = $(this);
          var $dialcode = $('#'+ $t.data('input-dial-code'));
          var $state = $('#'+ $t.data('input-state'));

          if ( $dialcode.length > 0 ) {
              var code = $t.find('option:selected').data('phone-code');
              console.log(code)
              if ( code == '' ) {
                  $dialcode.val('');
              } else {
                  $dialcode.val(code);
              }
          }

          if ( $state.length > 0 ) {
              var id   = $t.val();
              var html = '<option value="">Select</option>';

              $state.html(html);
              $state.trigger('change');

              if ( id != '' ) {
                  $.post(App.siteUrl('state/filter'), {
                      'country_id': id,
                  }, function(res) {
                      for (var i=0; i < res['states'].length; i++) {
                          html += '<option value="'+ res['states'][i]['id'] +'">'+ res['states'][i]['name'] +'</option>';
                          if ( i == res['states'].length-1 ) {
                              $state.html(html);
                           // $('.selectpicker').selectpicker('refresh')
                          }
                      }
                  });
              }
          }
      });

      // Load Cities on Province Change
      $('body').off('change', '[data-trigger="state-change"]');
      $('body').on('change', '[data-trigger="state-change"]', function() {
          var $t = $(this);
          var $city = $('#'+ $t.data('input-city'));

          if ( $city.length > 0 ) {
              var id   = $t.val();
              var html = '<option value="">Select</option>';

              $city.html(html);

              if ( id != '' ) {
                  $.post(App.siteUrl('city/filter'), {
                      'state_id': id,
                  }, function(res) {
                      for (var i=0; i < res['cities'].length; i++) {
                          html += '<option value="'+ res['cities'][i]['id'] +'">'+ res['cities'][i]['name'] +'</option>';
                          if ( i == res['cities'].length-1 ) {
                              $city.html(html);
                           // $('.selectpicker').selectpicker('refresh')
                          }
                      }
                  });
              }
          }
      });

      // Load Area on City Change
      $('body').off('change', '[data-trigger="city-change"]');
      $('body').on('change', '[data-trigger="city-change"]', function() {
          var $t = $(this);
          var $area = $('#'+ $t.data('input-area'));

          if ( $area.length > 0 ) {
              var id   = $t.val();
              var html = '<option value="">Select</option>';

              $area.html(html);

              if ( id != '' ) {
                  $.post(App.siteUrl('city/filter'), {
                      'state_id': id,
                  }, function(res) {
                      for (var i=0; i < res['cities'].length; i++) {
                          html += '<option  value="'+ res['cities'][i]['id'] +'">'+ res['cities'][i]['name'] +'</option>';
                          if ( i == res['cities'].length-1 ) {
                              $area.html(html);
                              //$('select').selectpicker('refresh');
                          }
                      }
                  });
              }
          }
          
      });
      $('body').off('change', '[data-trigger="area-filter"]');
      $('body').on('change', '[data-trigger="area-filter"]', function() {
          var $t = $(this);
          var $area = $('#'+ $t.data('input-area'));

          if ( $area.length > 0 ) {
              var id   = $t.val();
              var html = '<option value="">Select</option>';

              $area.html(html);

              if ( id != '' ) {
                  $.post(App.siteUrl('area/filter'), {
                      'city_id': id,
                  }, function(res) {
                      for (var i=0; i < res['cities'].length; i++) {
                          html += '<option  value="'+ res['cities'][i]['id'] +'">'+ res['cities'][i]['name'] +'</option>';
                          if ( i == res['cities'].length-1 ) {
                              $area.html(html);
                              //$('select').selectpicker('refresh');
                          }
                      }
                  });
              }
          }
          
      });

      

      // Load Stores on Company Change
      $('body').off('change', '[data-trigger="company-change"]');
      $('body').on('change', '[data-trigger="company-change"]', function() {
          var $t = $(this);
          var $store = $('#'+ $t.data('input-store'));
          var not_in = $(this).attr("data-not-in")||'';


          if ( $store.length > 0 ) {
              var id   = $t.val();
              var html = '<option value="">Select</option>';

              $store.html(html);

              if ( id != '' ) {
                  $.post(App.siteUrl('store/filter'), {
                      'company_id': id,
                      'not_in'    : not_in
                  }, function(res) {
                      for (var i=0; i < res['stores'].length; i++) {
                          html += '<option data-service-id="'+res['stores'][i]['service_id']+'" value="'+ res['stores'][i]['id'] +'">'+ res['stores'][i]['name'] +'</option>';
                          if ( i == res['stores'].length-1 ) {
                              $store.html(html);
                          }
                      }
                  });
              }
          }
      });

      // Disable keypress for datepicker input
      $('body').off('keypress', '[data-provide="datepicker"]');
      $('body').on('keypress', '[data-provide="datepicker"]', function(e) {
          e.preventDefault();
          return false;
      });
      $('body').on('change', '[data-role="file-image"]', function() {
          readURL(this, $(this).data('preview'));
      });

  }

  var readURL = function (input, previewId) {
      if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) {
              $('#' + previewId).prop('src', e.target.result);
          }
          reader.readAsDataURL(input.files[0]);
      }
  }

  var showToast = function ( msg ) {
      if ( msg.length > 0 && !Array.isArray(msg[0]) ) {
          msg = [[msg]];
      }
      $.each(msg, function(k,v) {
          if ( Array.isArray(v) && v[0] ) {
              var icon = v[1]||'info';
              if ( icon == 'danger' ) {
                  icon = 'error';
              }
              $.toast({
                  text: v[0]||'',
                  icon: icon,
                  position: 'bottom-right',
                  stack: 3,
                  textColor: 'white',
                  bgColor: '#1f262d',
                  loader: true,
                  loaderBg: '#D4AF37',
                  hideAfter: 5000,
              });
          }
      });
  }

  var showConfirmModal = function ( options, body, callback ) {
      if ( typeof(options) === 'string' ) {
          options = {
              title: options,
              body: body,
              onConfirm: callback,
          }
      }
      var options = $.extend({}, {
          title: 'Confirm Action',
          body: 'Are you sure that you want to proceed?',
          buttonText: 'Yes',
          onConfirm: function () {}
      }, options);

      var $modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+ options['title'] +'</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">'+ options['body'] +'</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary">'+ options['buttonText'] +'</button></div></div></div></div>');
      $modal.modal('show');

      $modal.off('click', '.btn-primary');
      $modal.on('click', '.btn-primary', function() {
          $modal.modal('hide');
          if ( typeof(options.onConfirm) === 'function' ) {
              options.onConfirm($modal);
          }
      });
  }

  var showAlertModal = function( message, title ) {
      var options = {
          title: title||'Alert',
          body: message,
          buttonText: 'Ok',
      };

      var $modal = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">'+ options['title'] +'</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">'+ options['body'] +'</div><div class="modal-footer"><button type="button" class="btn btn-primary">'+ options['buttonText'] +'</button></div></div></div></div>');
      $modal.modal('show');

      $modal.off('click', '.btn-primary');
      $modal.on('click', '.btn-primary', function() {
          $modal.modal('hide');
      });
  }

  var makeSafeName = function ( val, replacement ) {
      var str = typeof(val) !== 'undefined' ? String(val) : '';
      var rep = typeof(replacement) !== 'undefined' ? String(replacement) : '';
      var res = val.replace(/[^a-zA-Z0-9]/g, rep);
      res = res.replace(/([-_])\1+/g, '$1');
      res = res.replace(/^[\-_]+|[\-_]+$/g, "");
      res = res.trim();
      return res;
  }

  var loading = function ( action ) {
      if ( typeof action === 'undefined' ) {
          action = false;
      }

      var $loader = $('body').find('[data-role="ajax-loader"]');
      if ( $loader.length == 1 ) {
          if ( action === 'show' || action == true ) {
              $loader.show();
          } else {
              $loader.hide();
          }
      }
  }

  var handlejQueryValidationRules = function() {
      $('.jqv-input').each(function() {
          var $t = $(this);
          var rules = {};

          if ( $t.prop('readonly') ) {
              return; // equivalent to 'continue'
          }
          if ( $t.data('jqv-required') === true ) {
              rules['required'] = true;
          }
          if ( $t.data('jqv-email') === true ) {
              rules['email'] = true;
          }
          if ( $t.data('jqv-number') === true ) {
              rules['number'] = true;
          }
          if ( $t.data('jqv-min') ) {
              rules['min'] = $t.data('jqv-min');
          }
          if ( $t.data('jqv-max') ) {
              rules['max'] = $t.data('jqv-max');
          }
          if ( $t.data('jqv-maxlength') > 0 ) {
              rules['maxlength'] = $t.data('jqv-maxlength');
          }
          if ( $t.data('jqv-minlength') > 0 ) {
              rules['minlength'] = $t.data('jqv-minlength');
          }
          if (! $.isEmptyObject(rules) ) {
              $t.rules('add', rules);
          }
      });
  }
  
  $('.no_special_chars').bind('input', function() {
    var c = this.selectionStart,
        r = /[^a-z0-9 .]/gi,
        v = $(this).val();
    if(r.test(v)) {
      $(this).val(v.replace(r, ''));
      c--;
    }
    this.setSelectionRange(c, c);
  });

  return {
      init: function (options) {
          appInit(options);
          handleUI();
      },
      siteUrl: function ( uri ) {
          return siteUrl(uri);
      },
      baseUrl: function ( uri ) {
          return baseUrl(uri);
      },
      siteName: function () {
          return siteName();
      },
      toast: function ( msg ) {
          showToast(msg);
      },
      confirm: function ( title, body, callback ) {
          showConfirmModal(title, body, callback);
      },
      alert: function (message, title) {
          showAlertModal(message, title);
      },
      makeSafeName: function ( val, replacement ) {
          return makeSafeName(val, replacement);
      },
      initTreeView: function() {
          handleTreeView();
      },
      initFormView: function() {
          handleFormView();
      },
      loading: function( action ) {
          loading(action);
      },
      setJQueryValidationRules: function() {
          handlejQueryValidationRules(); // Handle valiation via data- attribute
      },
  }

})(jQuery);