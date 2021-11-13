<?php include($_SERVER['DOCUMENT_ROOT'].'/works/template/functions.php'); ?>
<?php
digest_auth(array(
        "hideki" => "hide0717",
        "shiho" => "Shippoda0",
        "works" => "chiba2018"
        ));
?>
<html>
  <head>
    <meta charset="utf-8">
    <title>業務フォーム</title>
    <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script-->
    <!--script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script-->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>

    <link rel="stylesheet" href="css/style.css?<?php echo date('Ymd-Hi'); ?>">
    <link rel="stylesheet" href="css/keiyaku.css?<?php echo date('Ymd-Hi'); ?>">
    <!--link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css" -->
    <!--link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-lightness/jquery-ui.css" -->
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css">

    <link rel="stylesheet" href="css/jquery.ui.timepicker.css">
    <script type="text/javascript" src="js/jquery.ui.timepicker.js?v=0.3.3"></script>

    <script>
      $(function() {
        $("#datepicker").datepicker({dateFormat: 'yy/mm/dd',});
        $('#timepicker').timepicker();
        $("#datepicker2").datepicker({dateFormat: 'yy/mm/dd',});
        $('#timepicker2').timepicker();
        $("#datepicker3").datepicker({dateFormat: 'yy/mm/dd',});
        $("#datepicker4").datepicker({dateFormat: 'yy/mm/dd',});
        $("#datepicker5").datepicker({dateFormat: 'yy/mm/dd',});
      });

      function MoveCheck(url) {
          if( confirm("本当にいいですか？") ) {
              window.location.href = 'http://' + location.hostname + '/works/' + url;
          }
          else {
              alert("キャンセルしました。");
              return false;
          }
      }


      //Form送信先を変えたい
      function setAction(url) {
          $('form').attr('action', url);

          $('form').submit();
      }

      //$('#searchorder [name=name]').val()
      //$(this).parents('form').attr('action', $(this).data('action'));
      $(function(){
          $('.submit').on('click', function(){
              var form = $(this).parents('form');
              form.attr('action', $(this).data('action'));
              form.submit();
          });
      })

    $(function() {
        $("#searchorder_name").autocomplete({
            source: "/works/template/ac_name.php"
        });
    });

    $(function() {
        var opts = {source: "/works/template/ac_zip.php",
                    focus: function(event, ui){
                        $('#zip_ac').val(ui.item.code);
                        $('#address_ac').val(ui.item.name);
                        return false;
                    },
                    select: function(event, ui){
                        $('#zip_ac').val(ui.item.code);
                        $('#address_ac').val(ui.item.name);
                        return false;
                    }};
        $("#zip_ac").autocomplete(opts).autocomplete('option', 'minLength', 0);
        $("#address_ac").autocomplete(opts).autocomplete('option', 'minLength', 0);
    });

    </script>
  </head>
  <body>
    <h1>業務システム</h1>
