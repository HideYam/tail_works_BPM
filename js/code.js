var myForm = document.getElementById('myForm');
myForm.addEventListener('submit', function(event) {
  event.preventDefault();

  var list = document.getElementById('list');
  while (list.firstChild) list.removeChild(list.firstChild);

  var elements = document.getElementsByTagName("input");
  for(var i=0; i<elements.length; i++){
    var type = elements[i].type;
    var name = elements[i].name;
    var key = elements[i].id;
    var checked = elements[i].checked;
    var value = elements[i].value;
    if(type == "checkbox" || type == "radio"){
      if (checked) {
        view(name, type, value, list);
      }
    }else {
      view(name, type, value, list);
    }
  }

}, false);

// 画面に表を作成する関数
var view = function(name, key, value, list) {
  var tr = document.createElement('tr');
  var td1 = document.createElement('td');
  var td2 = document.createElement('td');
  var td3 = document.createElement('td');
  list.appendChild(tr);
  tr.appendChild(td1);
  tr.appendChild(td2);
  tr.appendChild(td3);
  td1.innerText = key;
  td2.innerText = name;
  td3.innerText = value;
}
