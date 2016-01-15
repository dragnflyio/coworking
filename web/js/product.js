// Preview products
$('.btn-preview').click(function(){
  $.ajax({
    type: 'POST',
    url: '/product/import-preview',
    dataType: 'json',
    async: false
  })
  .done(function (data) {
    $('.content-preview').html(data);
  })
  return false;
});

// Save products from import
$('.import-action .btn-import').click(function(){
  $.ajax({
    type: 'POST',
    url: '/product/import-save',
    dataType: 'json',
    async: false
  })
  .done(function (data) {
    alert('Bạn đã nhập dữ liệu thành công');
  })
  return false;
});

// Save new product
$('.btn-submit').click(function(){
  $.ajax({
    type: 'POST',
    url: '/product/ajax-action',
    data: $('#f_product').serialize()
  })
  .done(function (data) {
    alert(data['message']);
    $('#f_product').trigger("reset");
  })
  return false;
});
