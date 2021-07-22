$.urlParam = function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if (results==null){
		   return null;
		}
		else{
		   return decodeURI(results[1]) || 0;
		}
	}
	
	
	
function cencel()
{
	$("#mapBlock").hide();
}

function addPot(recordId)
{ 
	if (window.confirm("Создать КП?")) { 
			document.location.href='index.php?module=Potentials&action=Convert&mode=CreateQFromPOT&leadid='+recordId;
		 	//document.location.href='index.php?module=Potentials&action=Convert&mode=CreateQ&leadid='+recordId;
	}
}


function selectRowAddr(id,name)
{
	$(id).val(name);
	$("#openPanelLI").remove();
}

function startSearch(ymaps,dataval,e)
{
	ymaps.suggest(dataval).then(function (items) {
		
			var name=$(e.target).attr("name");
		
			var id=$(e.target).attr("id");

			console.log(items);
			var test="";
			var li="";
			for(var index in items) {
				test=items[index];
				li=li+'<li onclick="selectRowAddr('+id+',\''+test.displayName+'\')" >'+test.displayName+'</li>';
			}
			
			if (document.getElementById("openPanelLI"))
			{ 
				$("#openPanelLI").html(""+li+"");				
			}
			else
			{
				$(e.target).after("<ul class='openPanelMenu hideY' id='openPanelLI' attr='"+name+"'>"+li+"</ul>");
			}			
	});
}

function setparam()
{
	var blockid=1;
	if ($.urlParam('module')=="Potentials"){
		 blockid=2;
	}
	var num=$("#idblock").val();
		
	var startpoint=$("#startpoint").val();
	var endpoint=$("#endpoint").val();
	
	var distance=$("#distance").val();
	var duration=$("#duration").val();
	var XY1=$("#XY1").val();
	var XY2=$("#XY2").val();

	var yandexWayPoint1=$("#yandexWayPoint1").val();
	$("[data-row-no="+num+"]").find("[data-fieldname=Timetable_from_coordinates]").val(XY1);
	$("[data-row-no="+num+"]").find("[data-fieldname=Timetable_where_coordinates]").val(XY2);
	$("[data-row-no="+num+"]").find("[data-fieldname=from_coordinates]").val(XY1);
	$("[data-row-no="+num+"]").find("[data-fieldname=where_coordinates]").val(XY2);
	$("[data-row-no="+num+"]").find("[data-fieldname=Timetable_name]").val(startpoint);
	$("[data-row-no="+num+"]").find("[data-fieldname=name]").val(startpoint);
	$("[data-row-no="+num+"]").find("[data-fieldname=where_address]").val(endpoint);
	$("[data-row-no="+num+"]").find("[data-fieldname=Timetable_where_address]").val(endpoint);
	$("#relatedblockslists_1_"+num+"_Timetable_distance").val(distance);
	$("[data-row-no="+num+"]").find("#relatedblockslists_"+blockid+""+num+"_Timetable_where_distance").val(distance);
	$("[data-row-no="+num+"]").find("#relatedblockslists_"+blockid+"_"+num+"_Timetable_duration").val(duration);
	$("[data-row-no="+num+"]").find("#relatedblockslists_"+num+"_"+blockid+"_Timetable_distance").val(distance);
	$("[data-row-no="+num+"]").find("#relatedblockslists_"+num+"_"+blockid+"_Timetable_duration").val(duration);
	$("#mapBlock").hide();
}

$(document).ready(function() {
	ymaps.ready(init);
});

function init() 
{
}

$(".openMap").live( "click", function() {
	//var num=$(this).attr("blockid");
	var num=$(this).closest('.relatedRecords').attr("data-row-no"); 
	$("#idblock").val(num);
	$("#mapBlock").show();  
}
);


function searchYndexData(e)
{
	$.ajax({
			url:'?module=Potentials&action=Convert&mode=sendRequestToYandexAPI',//&project='+$("#projectid").val()+'&pos='+trid+'&type='+param+'&typeinsert='+typeinsert, 
			success: function(data) 
			{
				if (data=="error")
				{
					console.log("Ошибка");
				}
				else
				{
					if (document.getElementById("autocomliteaddr"))
					{
						$("#autocomliteaddr").html($(e.target).val());
					}
					else
					{
						$(e.target).after("<div id='autocomliteaddr'>"+$(e.target).val()+"<br>"+data+"</div>");
					}
				}
			}
	});
}

var clearid="";

// В данном примере показано, как получить информацию о построенном маршруте,
// а также изменить его внешний вид.
ymaps.ready(function () {	
	  function getAddress(coords) {
        ymaps.geocode(coords).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
			console.log("Адрес: "+firstGeoObject.getAddressLine());
        });
    }

   var buttonEditor = new ymaps.control.Button({
        data: { content: "Режим редактирования" }
    });

    buttonEditor.events.add("select", function () {
        multiRoute.editor.start({
            addWayPoints: true,
            removeWayPoints: true
        });
    });

    buttonEditor.events.add("deselect", function () {
        // Выключение режима редактирования.
        multiRoute.editor.stop();
    });
	
  var myMap = new ymaps.Map('map', {
      center: [55.753994, 37.622093],
      zoom: 9,
      // Добавление панели маршрутизации на карту.
      controls: ['routePanelControl',buttonEditor]
  });
  
  myMap.controls
        // Кнопка изменения масштаба.
        .add('zoomControl', { left: 5, top: 5 });

  // Получение ссылки на панель.
  var control = myMap.controls.get('routePanelControl');


  control.options.set({
    // Список всех опций см. в справочнике.  
	Width: '600px',
    maxWidth: '600px',
    float: 'right'
  });
 
	
  control.routePanel.options.set({
    // Типы маршрутизации, которые будут отображаться на панели.
    // Пользователи смогут переключаться между этими типами.
		types: {
		   auto: true,
		   pedestrian: false,
		   // Добавление на панель
		   // значка «такси».
		   taxi: false
		}
		/**/
	});

    // Получение объекта, описывающего построенные маршруты.
    var multiRoutePromise = control.routePanel.getRouteAsync();
  
    multiRoutePromise.then(function(multiRoute) {		
    //  Подписка на событие получения данных маршрута от сервера.
    multiRoute.model.events.add('requestsuccess', function() {
 
	var test= multiRoute.getBounds();

	console.log(multiRoute.getWayPoints()); 

	var yandexWayPoint1 = multiRoute.getWayPoints().get(0).properties.get('address');
	console.log('yandexWayPoint1: '+yandexWayPoint1+"++++"); 
	
	var yandexWayPoint2 = multiRoute.getWayPoints().get(1).properties.get('address');
	console.log('yandexWayPoint2: '+yandexWayPoint2+"++++"); 
	
	
	var yandexCoords1 = multiRoute.getWayPoints().get(0).properties.get('coords');
	console.log('yandexWayPoint1: '+yandexCoords1+"++++"); 
	
	var yandexCoords2 = multiRoute.getWayPoints().get(1).properties.get('coords');
	console.log('yandexWayPoint2: '+yandexCoords2+"++++"); 
	
	
	$("#startpoint").val(yandexWayPoint1);
	$("#endpoint").val(yandexWayPoint2);
	
	
	 ymaps.geocode(yandexWayPoint1, {
        results: 1
    }).then(function (res) {
            // Выбираем первый результат геокодирования.
            var firstGeoObject = res.geoObjects.get(0);
               var coords = firstGeoObject.geometry.getCoordinates();
				console.log(coords);
				
				document.getElementById("XY1").value=coords;
				
		
	});

	 ymaps.geocode(yandexWayPoint2, {
        results: 1
    }).then(function (res) {
            // Выбираем первый результат геокодирования.
            var firstGeoObject = res.geoObjects.get(0);
               var coords = firstGeoObject.geometry.getCoordinates();
				console.log(coords);
				document.getElementById("XY2").value=coords;
	});

	if ((test[0][0]>0)&&(test[0][1]>0))
	{
		console.log('Все данные геообъекта: '+test[0]+"++++"+test[1]); 
	}
	
      // Ссылка на активный маршрут.
      var activeRoute = multiRoute.getActiveRoute();
	  var activeBOUNDS= multiRoute.getBounds();

      if (activeRoute) {
        // Вывод информации об активном маршруте.
		var distance=activeRoute.properties.get("distance").text;
		var duration=activeRoute.properties.get("duration").text;
		
		var type=activeRoute.properties.get("type");
		
		//59 мин.
		//1 ч 25 мин.
		
		if ((duration.indexOf("ч")>=0))
		{
			var timeweb=duration.split("ч");
			var hour=parseInt(timeweb[0])*60;
			var minute=parseInt(timeweb[1]);
			var time=hour+minute;
		//	alert(time);
		}
		else
		{
				var time=parseInt(duration);
		}
		
        console.log("!!!Длина: " + activeRoute.properties.get("distance").text);
        console.log("!!!Время прохождения: " + activeRoute.properties.get("duration").text);
		console.log("!!!Тупе: " + activeRoute.properties.get("type").text);
		
		document.getElementById("distance").value=distance; 
		document.getElementById("duration").value=time;
      }
    });
    multiRoute.options.set({
      // Цвет метки начальной точки.
      wayPointStartIconFillColor: "#B3B3B3",
      // Цвет метки конечной точки.
      wayPointFinishIconFillColor: "blue", 
      // Внешний вид линий (для всех маршрутов).
      routeStrokeColor: "00FF00"
    });  
  }, function (err) {
    console.log(err); 
  });
});

$(document).on("click", "[data-fieldname=cf_1233]", function(e)
{ 
	$("#mapBlock").show(); 
}); 