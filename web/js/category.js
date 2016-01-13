// Save category into database
$(".btn-save").click(function(e){
  $.ajax({
    type: 'POST',
    url: '/category/ajax-action',
    data: $('#f_category').serialize()
  })
  .done(function (data) {
    alert('Thêm thành công danh mục');
    $('#f_category').trigger("reset");
  })
  return false;
});
