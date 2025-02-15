$(document).ready(function () {
    let draw = Chart.controllers.line.__super__.draw;

    const lineChart_3 = document.getElementById("lineChart_3").getContext('2d');

    Chart.controllers.line = Chart.controllers.line.extend({
        draw: function () {
            draw.apply(this, arguments);
            let nk = this.chart.chart.ctx;
            let _stroke = nk.stroke;
            nk.stroke = function () {
                nk.save();
                nk.shadowBlur = 10;
                nk.shadowOffsetX = 0;
                nk.shadowOffsetY = 0;
                _stroke.apply(this, arguments)
                nk.restore();
            }
        }
    });

    lineChart_3.height = 100;

    let datasets = [];
    for (let [key, statistic] of Object.entries(statistics)) {
        datasets.push({
            label: statistic.name,
            data: statistic.data,
            borderColor: statistic.color,
            borderWidth: "2",
            backgroundColor: 'transparent',
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent',
            pointHoverBackgroundColor: statistic.color,
            pointBorderWidth: 30
        })
    }

    new Chart(lineChart_3, {
        type: 'line',
        data: {
            defaultFontFamily: 'Poppins',
            labels: range,
            datasets: datasets
        },
        options: {
            legend: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 800,
                        min: 0,
                        stepSize: 50,
                        padding: 10
                    },
                    gridLines: {
                        color: "rgba(255, 255, 255,0.05)",
                        drawBorder: true
                    }
                }],
                xAxes: [{
                    gridLines: {
                        color: "rgba(255, 255, 255,0.05)",
                        drawBorder: true
                    },
                    ticks: {
                        padding: 5
                    }
                }]
            },

        }
    });

    $('input[name="planStatisticDate"]').on('change', function () {
        $('#statistics_range').submit();
    })

})