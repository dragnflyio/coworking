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
