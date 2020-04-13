google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    var data = new google.visualization.DataTable();


    data.addColumn('date', 'X');
    data.addColumn('number', 'Dogs');
    data.addColumn('number', 'Cats');

    data.addRows(__DATA__);

    var options = {
        height:600,
//        chartArea: {
//            height:300,
//            top:100,
//        },
        colors: ['#10a513', '#097138'],
        //       crosshair: {
        //           color: '#000',
        //           trigger: 'selection'
        //       },

        hAxis: {
            title: 'Time',
            format: 'yy-MM-dd HH:mm',
//            slantedText:true,
//            slantedTextAngle:90
        },

        vAxis: {
            title: 'Popularity'
        },
    };

    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

    chart.draw(data, options);
    //chart.setSelection([{row: 38, column: 1}]);

}
