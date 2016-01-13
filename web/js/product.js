$('.btn-import').click(function(){
  alert('ok');
  $.ajax({
    type: 'GET',
    url: '/product/import-ajax',
    dataType: 'json',
    async: false
  })
  .done(function (data) {
    $('.content-preview').html(data);
  })
  return false;
});

