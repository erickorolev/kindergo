$.urlParam = function(name){
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if (results==null){
	   return null;
	}
	else{
	   return decodeURI(results[1]) || 0;
	}
}
	
function cancel()
{
	$("#mapBlock").hide();
	$("#mapBlockGetCoordinat").hide();
}

function closePanel(namePanel)
{
	$("#"+namePanel).hide();
}

function addInvoice(recordId)
{ 
	if (window.confirm("Создать Счет?")) { 
		document.location.href='index.php?module=Potentials&action=Convert&mode=CreateInvoiceFromPOT&quoteid='+recordId;
	}
}



function addPot(recordId)
{ 
	if (window.confirm("Создать КП?")) { 
		document.location.href='index.php?module=Potentials&action=Convert&mode=CreateQFromPOT&leadid='+recordId;
	}
}


function addTrips(recordId)
{
	$.ajax({
		url:'?module=Potentials&action=Convert&mode=checkTrip&recordId='+recordId,
		success: function(data) 
		{
			if (data==1)
			{
				if (window.confirm("Создать Поездки?")) { 
					$("#messageTrip").show();
					document.location.href='index.php?module=Potentials&action=Convert&mode=CreateTripsFromPOT&recordid='+recordId;
				}
			}
			else
			{
				alert("Для создании поездок необходимо заполнить поля: ”Описание”, “Информация о парковке”, ”Ребенок 1");
				return false;
			}
		}
	});
}



function selectRowAddr(id,name)
{
	$(id).val(name);
	$("#openPanelLI").remove();
}

function startSearch(ymaps,dataval,e)
{
	/*
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
	/**/
}

function setparam()
{
	let num=$("#idblock").val();
	let endpoint=$("#startpoint").val();
	let startpoint=$("#endpoint").val();	
	let distanceNameId=$("#distance_id").val();
	let durationNameId=$("#duration_id").val();
	let distance=$("#distance").val();
	let duration=$("#duration").val();
	let XY1=$("#XY1").val();
	let XY2=$("#XY2").val();
	let yandexWayPoint1=$("#yandexWayPoint1").val();
	
	$("[data-row-no="+num+"]").find("[data-fieldname=Timetable_name]").val(startpoint);
	$("[data-row-no="+num+"]").find("[data-fieldname=name]").val(startpoint);
	$("[data-row-no="+num+"]").find("[data-fieldname=where_address]").val(endpoint);
	$("[data-row-no="+num+"]").find("[data-fieldname=Timetable_where_address]").val(endpoint);
	$("#"+distanceNameId).val(distance.replace('км', ''));
	$("#"+durationNameId).val(duration);
	$("#mapBlock").hide();
}

$(document).ready(function() {
	ymaps.ready(init);
});

function init() 
{
}

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



function saveCalendar()
{
	var recordFieldName=$("#recordFieldName").val();
	var dateStr="";
	var countday=0;
	$('.selectDay').each(function (index, value) {	
		countday++;
		var day=$(value).html();
		var year=$(value).closest(".blockCalendar").find(".currentYear").html(); 
		var monthNum=$(value).closest(".blockCalendar").find(".monthNum").val(); 
		if (monthNum.length==1){monthNum="0"+monthNum;}
		if (day.length==1){day="0"+day;}
		dateStr=day+"-"+monthNum+"-"+year+", "+dateStr;
	});
	
	
	//countday
	$("#"+recordFieldName.replace('_date', '_trips')).val(countday);
	$("#"+recordFieldName).val(dateStr.trim());
	$("#openCalendar").hide();
}

function addCalendar(type,value,countMonth)  
{
	$("#addNewCalendar").hide();
	$("#button3").html("Загрузка...");
	
	var count=parseInt($("#countCalendar").val());
	$("#openCalendar").show();  

	var currentDate = new Date();	
	if (count>0)
	{
		var month= count+1;
	}
	else
	{
		var month= currentDate.getMonth()+1;
	}

	var currentDateConst=$("#currentDate").val().split("-");
	var year=$("#yearTemp").val();
	if ((year!=undefined)&&(year!=""))
	{
		
	}
	else
	{
		year=currentDateConst[0];
	}
	
	if (month==13){  year=parseInt(year)+1; month=1; count=0; $("#yearTemp").val(year); } 
	count=month;
	
	$("#countCalendar").val(count);

	var arr=[
	   'Январь',
	   'Февраль',
	   'Март',
	   'Апрель',
	   'Май',
	   'Июнь',
	   'Июль',
	   'Август',
	   'Сентябрь',
	   'Октябрь',
	   'Ноябрь',
	   'Декабрь',
	];

	$.ajax({
		url:'?module=Potentials&action=Convert&mode=createCalendar&month='+month+'&year='+year+'&count='+count+'&value='+value, 
		success: function(data) 
		{
			if (data=="error")
			{
				console.log("Ошибка");
			}
			else
			{
				$(".containerCalendar").append(data);  
			}
			
			countMonth=countMonth-1;
			
			if (countMonth>0)
			{
				addCalendar(0,value,countMonth)  
			}
			
			$("#addNewCalendar").show();
			$("#button3").html("");
		}
	});
} 

/*
$("[data-fieldname=date]").live( "click", function() 
{
	let currentDateConst=$("#currentDate").val().split("-");
	$("#yearTemp").val("");
	$("#countCalendar").val(""); 

	$("#openCalendar").show();  
	if($('*').is('.blockCalendar')) 
	{
		$(".blockCalendar").remove();
		$("#countCalendar").val("0");
		
		let year="",month="",currentdate="";
		let value=$(this).val();
		
		if (value!="")
		{	
			let splitData=value.split(",");
			if (splitData.length>1)
			{
				for (var i=0;i<splitData.length;i++)
				{
					currentdate=splitData[1].split("-");
				}
			}
		}		
		addCalendar(1,$(this).val(),1);
	}
	else
	{
		addCalendar(0,"",1);
	}
});/**/


$(".b-calendar__number").live( "click", function() {
	if ($(this).is(".selectDay"))
	{
		$(this).removeClass("selectDay");
		
	}
	else
	{
		$(this).addClass("selectDay");
	}
});

$(".openCalendar").live( "click", function() 
{
	let date1=$(this).closest('.relatedRecords').find("[data-fieldname=Timetable_date]").val();
	let date2=$(this).closest('.relatedRecords').find("[data-fieldname=date]").val(); //data-fieldname="date"
	let recordSelectField,date="";
	let num=$(this).closest('.relatedRecords').attr("data-row-no"); 
	
	if (date1!=undefined)
	{
		recordSelectField=$(this).closest('.relatedRecords').find("[data-fieldname=Timetable_date]").attr("id");
		date=date1;
	}
	if (date2!=undefined)
	{
		recordSelectField=$(this).closest('.relatedRecords').find("[data-fieldname=date]").attr("id"); 
		date=date2;
	}
	
	$("#recordFieldName").val(recordSelectField);
	$("#idblock").val(num); 
	$("#openCalendar").show();  
	$("#countCalendar").val("0");
	$(".blockCalendar").remove();
	$("#yearTemp").val("");
	
	let countmonth=date.split(",");	
	let currentDate=$("#currentDate").val().split("-");
	let sorted = countmonth.slice() // copy the array for keeping original array with order
	  // sort by parsing them to date
	  .sort(function(a, b) {
		return new Date(a) - new Date(b);
	  });
	let temp="",tempmonth=0,tempyear=0,lastDate="";
	
	for (var i=0;i<=countmonth.length;i++)
	{
		if ((countmonth[i]!="")&&(countmonth[i]!=undefined))
		{
			temp=countmonth[i].split("-");

			if (((temp[1]>tempmonth)&&(temp[2]>=tempyear))) //||((temp[1]>tempmonth)&&(temp[2]==tempyear))
			{
				tempmonth=temp[1];
				tempyear=temp[2];
			}
			lastDate=sorted[i];
		}
	}

	let monthtemp="27-"+tempmonth+"-"+tempyear;
	let dayexp=lastDate.split("-");
	
	dayexp[1]=tempmonth;
	dayexp[2]=tempyear;

	let countMonth=
	monthDiff(
		new Date(currentDate[0], currentDate[1], currentDate[2]), // November 4th, 2008
		new Date(dayexp[2], dayexp[1], dayexp[0])  
	); 
	countMonth=countMonth+1;
	addCalendar(0,date,countMonth);
}
);


function monthDiff(dateFrom, dateTo) {
 return dateTo.getMonth() - dateFrom.getMonth() + 
   (12 * (dateTo.getFullYear() - dateFrom.getFullYear()))
}

function saveCoord(coordinat)
{
	$("#mapBlockGetCoordinat").hide();
	$("#Contacts_editView_fieldName_attendant_coordinates").val(coordinat);
}

function selectContact(info1,info2)
{
	$("#mapBlockGetCoordinat").hide();
	$("[name=cf_nrl_contacts59_id]").val(info1);
	$("#cf_nrl_contacts59_id_display").val(info2);
}

function openMap(recordId,module)
{
	$.ajax({
		url:'?module=Potentials&action=Convert&mode=getCoord&record='+recordId+'&mod='+module,
		success: function(data) 
		{
			if (data=="error")
			{
				console.log("Ошибка");
			}
			else
			{
				var data3=data.split("||");
				var data2=data3[1].split("::");
				
				var geokodelist=data3[0].split("##");
				
				ymaps.ready(function () {
				var myMap = new ymaps.Map('mapCoordinat', {
						center: [55.751574, 37.573856],
						zoom: 9,
						controls: ['smallMapDefaultSet']
					}, {
						searchControlProvider: 'yandex#search'
					});
					

				  for (var i=0;i<=data2.length;i++)
				  {
						
						if ((data2[i]!="")&&(data2[i]!=undefined))
						{
							let info=data2[i].split("##");
							let coord=info[2].split(",");
							  myPlacemark = new ymaps.Placemark([coord[0], coord[1]], { balloonContent: '<a href="?module=Contacts&view=Detail&record='+info[0]+'" target=="_blank">'+info[1]+'</a><br><a href="javascript:selectContact(\''+info[0]+'\',\''+info[1]+'\')">Выбрать</a>'}, {iconLayout: 'default#image',});
							  myMap.geoObjects.add(myPlacemark);
				   
						}
				  }
				
				  // myPlacemark = new ymaps.Placemark([55.753994, 37.622093], { balloonContent: 'Это красивая метка'}, {iconLayout: 'default#image',});
				  // myPlacemark2 = new ymaps.Placemark([55.853994, 37.822093], { balloonContent: 'Это красивая метка'}, { preset: 'islands#redDotIcon' });
				   //myMap.geoObjects.add(myPlacemark);


				for (var i=0;i<=geokodelist.length;i++)
				{			  
					if (geokodelist[i]!="")
					{
						ymaps.geocode(geokodelist[i], {
							results: 1
						}).then(function (res) {
								var firstGeoObject = res.geoObjects.get(0),
									coords = firstGeoObject.geometry.getCoordinates(),
									bounds = firstGeoObject.properties.get('boundedBy');

								firstGeoObject.options.set('preset', 'islands#redDotIcon');
								firstGeoObject.properties.set('iconCaption', firstGeoObject.getAddressLine());

								// Добавляем первый найденный геообъект на карту.
								myMap.geoObjects.add(firstGeoObject);
						});
					}
				}
				  
				  
				});
			}
		}
	});
	$("#mapBlockGetCoordinat").show();  

}


$("#Contacts_editView_fieldName_attendant_coordinates").live( "click", function() {
	ymaps.ready(function () {
    var myMap = new ymaps.Map('mapCoordinat', {
            center: [55.751574, 37.573856],
            zoom: 9,
            // Также доступны наборы 'default' и 'largeMapDefaultSet'
            // Элементы управления в наборах подобраны оптимальным образом
            // для карт маленького, среднего и крупного размеров.
            controls: ['smallMapDefaultSet']
        }, {
            searchControlProvider: 'yandex#search'
        });
		
		
	// Обработка события, возникающего при щелчке
    // левой кнопкой мыши в любой точке карты.
    // При возникновении такого события откроем балун.
    myMap.events.add('click', function (e) {
        if (!myMap.balloon.isOpen()) {
            var coords = e.get('coords');
            myMap.balloon.open(coords, {
                contentHeader:'Координаты',
                contentBody:'' +
                    [
                    coords[0].toPrecision(6),
                    coords[1].toPrecision(6)
                    ].join(', ') + '</p>',
                contentFooter:'<a href="javascript:saveCoord(\''+ coords[0].toPrecision(6)+','+coords[1].toPrecision(6)+'\')">Сохранить</a>'
            });
        }
        else {
            myMap.balloon.close();
        }
    });	
    });
	$("#mapBlockGetCoordinat").show();  
	}
);


$("#cf_nrl_contacts59_id_display").live("click", function() 
	{
		let recordId=0;
		recordId=$("#recordId").val();
		if (!recordId>0){ recordId=$.urlParam('record'); }
		openMap(recordId,"trips");
	}
);

$(".openUser").live("click", function() 
	{
		//let recordId=0;
		//recordId=$("#recordId").val();
		//if (!recordId>0){ recordId=$.urlParam('record'); }
		openMap($("#recordId").val(),"potential");
	}
);


$("#Contacts_editView_fieldName_attendant_coordinates").live("click", function() {
	ymaps.ready(function () {
    var myMap = new ymaps.Map('mapCoordinat', {
            center: [55.751574, 37.573856],
            zoom: 9,
            // Также доступны наборы 'default' и 'largeMapDefaultSet'
            // Элементы управления в наборах подобраны оптимальным образом
            // для карт маленького, среднего и крупного размеров.
            controls: ['smallMapDefaultSet']
        }, {
            searchControlProvider: 'yandex#search'
        });
		
		
	// Обработка события, возникающего при щелчке
    // левой кнопкой мыши в любой точке карты.
    // При возникновении такого события откроем балун.
    myMap.events.add('click', function (e) {
        if (!myMap.balloon.isOpen()) {
            var coords = e.get('coords');
            myMap.balloon.open(coords, {
                contentHeader:'Координаты',
                contentBody:'' +
                    [
                    coords[0].toPrecision(6),
                    coords[1].toPrecision(6)
                    ].join(', ') + '</p>',
                contentFooter:'<a href="javascript:saveCoord(\''+ coords[0].toPrecision(6)+','+coords[1].toPrecision(6)+'\')">Сохранить</a>'
            });
        }
        else {
            myMap.balloon.close();
        }
    });
	
		
    });
	$("#mapBlockGetCoordinat").show();  
}
);



$(".openMap").live( "click", function() 
{
	$("#mapcontainer").html('<div id="map" style="width: 100%; height: 100%"></div>');
	
	
	let recordSelectField="",recordSelectField2="";
	recordSelectField=$(this).closest('.relatedRecords').find("[data-fieldname=Timetable_duration]").attr("id");
	if (recordSelectField==undefined)
	{
		recordSelectField=$(this).closest('.relatedRecords').find("[data-fieldname=duration]").attr("id");
	}
	$("#duration_id").val(recordSelectField);
	recordSelectField2=$(this).closest('.relatedRecords').find("[data-fieldname=Timetable_distance]").attr("id"); 
	if (recordSelectField2==undefined)
	{
		recordSelectField2=$(this).closest('.relatedRecords').find("[data-fieldname=distance]").attr("id");	
	}
	$("#distance_id").val(recordSelectField2);
	let num=$(this).closest('.relatedRecords').attr("data-row-no"); 
	
	$("#idblock").val(num);
	
	ymaps.ready(function () {	
		  function getAddress(coords) {
			ymaps.geocode(coords).then(function (res) {
				var firstGeoObject = res.geoObjects.get(0);
				console.log("Адрес: "+firstGeoObject.getAddressLine());
			});
		}


	   var buttonEditor = new ymaps.control.Button({
			data: { content: "Режим редактирования" },
		   options: { visible:false, float: 'none', position: {left: '-5px', top: '-5px'} }
		});
	
		buttonEditor.events.add("click", function () {
			multiRoute.editor.start({
				addWayPoints: true,
				removeWayPoints: true
			});
		});

	
		buttonEditor.events.add("click", function () {
			// Выключение режима редактирования.
			multiRoute.editor.stop();
		});
		/**/
		
		
		
		 // Создаем кнопки для управления мультимаршрутом.
		var trafficButton = new ymaps.control.Button({
			data: { content: "Сохранить" },
			options: {/*selectOnClick: true,/**/ width:"150",  maxWidth: [30, 100, 150]}
		}),
		viaPointButton = new ymaps.control.Button({
			data: { content: "Закрыть" },
			options: { /*selectOnClick: true/**/ }
		});

		viaPointButton.events.add('select', function () {
			cancel();
			
			$("#map").remove();
			//myMap.geoObjects.remove(myMap);
		});

		// Объявляем обработчики для кнопок.
		trafficButton.events.add('select', function () {
			setparam();
		});



	  var myMap = new ymaps.Map('map', {
		  center: [55.753994, 37.622093],
		  zoom: 9,
		  // Добавление панели маршрутизации на карту.
		  controls: ['routePanelControl',buttonEditor,trafficButton, viaPointButton]
	  },{
		   buttonMaxWidth: 300
	  }); 
	  
	  myMap.controls
			// Кнопка изменения масштаба.
			.add('zoomControl', { left: 5, top: 0 });

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
			var yandexWayPoint1 = multiRoute.getWayPoints().get(0).properties.get('address');
			var yandexWayPoint2 = multiRoute.getWayPoints().get(1).properties.get('address');
			var yandexCoords1 = multiRoute.getWayPoints().get(0).properties.get('coords');
			var yandexCoords2 = multiRoute.getWayPoints().get(1).properties.get('coords');

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
	$("#mapBlock").show();  
}
);

$(document).on("click", "[data-fieldname=cf_1233]", function(e)
{ 
	$("#mapBlock").show(); 
}); 

$(document).ready(function(){
	$("[data-fieldname=date]").attr("autocomplete","off");
});