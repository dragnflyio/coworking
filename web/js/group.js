jQuery(function(){
  // Validation group
  $('.numeric').intOnly();

  // Add/Update group.
  $('.btn-submit').click(function(e){
    e.preventDefault();
    if (beforePost() == true) {
      $.ajax({
        type: 'POST',
        url: '/group/ajax-action',
        data: $('#f_group').serialize()
      })
      .done(function (data) {
        alert(data['message']);
        $('#f_group').trigger("reset");
      })
      return false;
    }
  });

  // Search group.
  function doSearch(){
    ajaxPost('/group/search', $('#f_groupsearch').serialize(), function(data){
      if (!data.empty){
        console.log(data);
        $('#list-group').html(mr( $('#template_table').html(), {list:data}))
      } else {
        $('#list-group').html('<tr><th colspan="6" scope="row" class="text-center">'+ data.empty + '</th></tr>');
      }
    });
  }

  // Init doSearch().
  doSearch();

  $('#btnsearch').click(function(e){
    e.preventDefault();
    $('#list-group').html('<tr><th colspan="6" scope="row" class="text-center"><i class="glyphicon glyphicon-refresh fa-spin"></i></th></tr>');
    doSearch();
  })
})
