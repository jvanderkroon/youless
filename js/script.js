var loadingEnabled = false;

// Live chart function
var tmpWatt = 0;

function requestLiveData() {
    $.ajax({
        url: 'ajax.php?a=live',
        dataType: 'json',
        success: function(json) {
			var interval = $('#settingsOverlay').data('liveinterval');
			var shiftMax = 60000 / interval;
            var series = chart.series[0],
                shift = series.data.length > shiftMax; // shift if the series is longer than shiftMax

            // add the point
            var x = (new Date()).getTime();
            var y = json["pwr"];
            //console.log(point);
            chart.series[0].addPoint([x, y], true, shift);
            
            // up/down indicator
            if(tmpWatt < parseInt(json["pwr"])){
            	updown = "countUp";
            }
            else if(tmpWatt == parseInt(json["pwr"])){
            	updown = "";
            }
            else
            {
            	updown = "countDown";
            }
            tmpWatt = parseInt(json["pwr"]);
            
            // update counter
            $('#wattCounter').html("<span class='"+updown+"'>"+json["pwr"]+" Watt</span>");            
            
            // call it again after one second
            setTimeout(requestLiveData, interval);    
        },
        cache: false
    });
}
// Calculate costs/kwh function
function calculate(target, date){
		$('#kwhCounter').html("<span style='line-height:30px;font-style:italic;'>Loading…</span>");
		$('#cpkwhCounter').html("<span style='line-height:30px;font-style:italic;'>Loading…</span>");	
			
		$.ajax({
			url: 'ajax.php?a=calculate_'+target+'&date='+date,
			dataType: 'json',
			success: function( jsonData ) {
			
					// KWH and costs counter
					if($('input[name=dualcount]:checked').val() == 1)
					{
						$('#kwhCounter').html("<span>H: "+jsonData["kwh"]+" kWh<br>L: "+jsonData["kwhLow"]+" kWh<br>T: "+jsonData["totalKwh"]+" kWh</span>");
						$('#cpkwhCounter').html("<span>H: € "+jsonData["price"]+" <br>L: € "+jsonData["priceLow"]+" <br>T: € "+jsonData["totalPrice"]+"</span>");
					}
					else
					{
						$('#kwhCounter').html("<span style='line-height:30px;'>"+jsonData["kwh"]+" kWh</span>");
						$('#cpkwhCounter').html("<span style='line-height:30px;'>€ "+jsonData["price"]+"</span>");
					}				
			},
			cache: false
		});	
}				
		
// Create chart function
function createChart(target, date){

			// Generate loading screen
			if(loadingEnabled)
			{
				historychart.showLoading();
			}
			else
			{
				loadingEnabled = true;
			}								
							
			$.ajax({
				url: 'ajax.php?a='+target+'&date='+date,
				dataType: 'json',
				success: function( jsonData ) {

					// If invalid data give feedback
					if(jsonData["ok"] == 0)
					{
						$('#message').text(jsonData["msg"]);
						$('#overlay').fadeIn();
					}
					
						// Format data
						jsDate = jsonData["start"].split("-");
						year = jsDate[0];
						month = jsDate[1]-1;
						day = jsDate[2]-0;
						
						var start = (new Date(year, month, day)).getTime();

						if(target == 'week')
						{
							var title = 'Weekverbruik';
							var type = 'areaspline';
							var serieName = 'Watt';
							var yTitle = {
				                text: 'Watt',
				                margin: 40
				            };					
							var rangeSelector = false;
							var navScroll = true;
							var pointInterval = 60 * 1000;
							var tickInterval = null;
							var plotLines = [{
								value: start + (24 * 60 * 60 * 1000),
								width: 1, 
								color: '#c0c0c0'
							},{
								value: start + (2 * 24 * 60 * 60 * 1000),
								width: 1, 
								color: '#c0c0c0'
							},{
								value: start + (3 * 24 * 60 * 60 * 1000),
								width: 1, 
								color: '#c0c0c0'
							},{
								value: start + (4 *24 * 60 * 60 * 1000),
								width: 1, 
								color: '#c0c0c0'
							},{
								value: start + (5 * 24 * 60 * 60 * 1000),
								width: 1, 
								color: '#c0c0c0'
							},{
								value: start + (6 * 24 * 60 * 60 * 1000),
								width: 1, 
								color: '#c0c0c0'
							}];										
						}
						else if(target == 'day')
						{
							var title = 'Dagverbruik';
							var type = 'areaspline';
							var serieName = 'Watt';
							var yTitle = {
				                text: 'Watt',
				                margin: 40
				            };			
							var rangeSelector = false;
							var navScroll = true;
							var pointInterval = 60 * 1000;
							var tickInterval = null;
							var plotLines = null;											
						}
						else if(target == 'month')
						{
							var title = 'Maandverbruik';
							var type = 'column';
							var serieName = 'kWh';
							var yTitle = {
				                text: 'Kilowattuur',
				                margin: 40
				            };
							var rangeSelector = false;
							var navScroll = false;
							var pointInterval = 24 * 60 * 60 * 1000;
							var tickInterval = 48 * 60 * 60 * 1000;
							var plotLines = null;
							var buttons = [];
						}
												
						
						// Parse values to integers
						data = jsonData["val"].split(",");
						for(var i=0; i<data.length; i++) { data[i] = parseFloat(data[i], 10); } 
						
						// Create the chart
						historychart = new Highcharts.StockChart({
							chart : {
								renderTo : 'history',
								type: type			
							},			
							rangeSelector : {
								buttons: buttons						
							},
							credits: {
								enabled: false
							},
							title : {
								text : title
							},	
							yAxis:{
								showFirstLabel: false,
								title: yTitle
							},
							xAxis: {
								type: 'datetime',
								tickInterval: tickInterval,
								plotLines: plotLines
							},	
							rangeSelector:{
								enabled: rangeSelector
							},							
							navigator:{
								enabled: navScroll
							},									
							scrollbar:{
								enabled: navScroll
							},						
							series : [{
								name : serieName,
								turboThreshold: 5000,
								data : data ,
								pointStart: start,
				            	pointInterval: pointInterval,
								tooltip: {
									valueDecimals: 2
								}
							}]
						});	
						
						calculate(target, date);											
						
				},
    			cache: false
			});
			
						
}		

			
$(document).ready(function() {

	// Dialogs (alerts)
	$('#closeDialog').click(function(){
		$('#overlay').hide();
	});
		
	// Settings
	$('#showSettings').click(function(){
		$('#settingsOverlay').slideDown();
	});
	$('#hideSettings').click(function(){
		$('#settingsOverlay').slideUp(function(){
			var dualcnt = $('input[name=dualcount]:checked').val();
			if(dualcnt != $('#settingsOverlay').data('dualcount'))
			{
				$('input[name=dualcount]').not(':checked').attr('checked', true);
				if($('#settingsOverlay').data('dualcount') == 1)
				{
					$('.cpkwhlow').show();
				}
				else
				{
					$('.cpkwhlow').hide();
				}
			}		
		});		
	});
	
	$('input[name=dualcount]').change(function(){
		var dualcnt = $('input[name=dualcount]:checked').val();
		if(dualcnt == 1)
		{
			$('.cpkwhlow').show();
		}
		else
		{
			$('.cpkwhlow').hide();
		}
	});
		
	$('#saveSettings').click(function(){
		$.ajax({
			url: 'ajax.php?a=saveSettings',
			type: 'POST',
			dataType: 'json',
			data: $('#settingsOverlay form').serialize(),
			success: function( data ) {

				$('#settingsOverlay').slideUp('fast', function(){
					$('#settingsOverlay input[type=password]').val('');
				});
				
				if($('#settingsOverlay').data('dualcount') != $('input[name=dualcount]:checked').val())
				{
					$('#settingsOverlay').data('dualcount', $('input[name=dualcount]:checked').val());	
					var chart = $('#history').data('chart');
					calculate(chart, $('#datepicker').val());					
				}
				$('#settingsOverlay').data('liveinterval', $('select[name=liveinterval]').val());								

				$('#message').text(data["msg"]);
				$('#overlay').fadeIn();			
			}
		});			
		return false;
	});	
	
	// Show chart
	$('.showChart').click(function(){
		var chart = $(this).data('chart');
		$('.chart').hide();
		$('.'+chart).show();
		
		$('.btn li').each(function(){
			$(this).removeClass('selected');
		});
		$(this).parent().addClass('selected');
		$('#history').data('chart', chart);
		
		if(chart != 'live')
		{
			createChart(chart, $('#datepicker').val());		
		}
		//console.log(chart);
	});
	
	
	//Highcharts options
	Highcharts.setOptions({
		global: {
			useUTC: false
		},	
		lang: {
			decimalPoint: ',',
			months: ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'],
			shortMonths: ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
			weekdays: ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag']
		}			
	});
	
	// Live chart
    chart = new Highcharts.Chart({
        chart: {
            renderTo: 'live',
            defaultSeriesType: 'areaspline',
            events: {
                load: requestLiveData
            }
        },         
		credits: {
			enabled: false
		},
		legend: {
			enabled: false
		},		      
        title: {
            text: 'Actueel verbruik'
        },
        xAxis: {
            type: 'datetime',
            tickPixelInterval: 150,
            minRange: 60 * 1000
        },
        yAxis: {
			showFirstLabel: false,
            minPadding: 0.2,
            maxPadding: 0.2,
            title: {
                text: 'Watt',
                margin: 40
            }
        },
        series: [{
            name: 'Watt',
            data: []
        }],
		exporting: {
			enabled: false
		}		
    });  
	
		
	// Datepicker
	$('#datepicker').datepicker({
		inline: true,
		dateFormat: 'yy-mm-dd',
		maxDate: new Date(),
		showOn: 'focus',
		//changeMonth: true,
		//changeYear: true,	
		firstDay: 1,	
		monthNames: ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December'],
        monthNamesShort: ['jan', 'feb', 'maa', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
        dayNames: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
        dayNamesShort: ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'],
        dayNamesMin: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
		onSelect: function(date, inst){
		
			
			var target = $('#history').data('chart');			
			createChart(target, date);


		}		
	});
			
	      
});