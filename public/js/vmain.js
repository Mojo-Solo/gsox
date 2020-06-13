$('.selectpicker').selectpicker().ajaxSelectPicker({
  ajax: {
    url: document.location.origin+'/search/vendors',
    type: 'POST',
    dataType: 'json',
    data: {
      q: '{{{q}}}'
    }
  },
  bindEvent:'keyup',
  cache:true,
  clearOnError:true,
  clearOnError:true,
  locale:{emptyTitle: 'Search and Select Vendor'},
  preprocessData: function (data) {
    var i, l = data.length, array = [];
    if (l) {
        for (i = 0; i < l; i++) {
            array.push($.extend(true, data[i], {
                text : data[i].company_name,
                value: data[i].id,
                data : {
                    subtext: ''
                }
            }));
        }
    }
    return array;
  }
});