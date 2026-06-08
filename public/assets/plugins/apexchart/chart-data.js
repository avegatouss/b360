'use strict';

$(document).ready(function () {

  function generateData(baseval, count, yrange) {
    var i = 0;
    var series = [];
    while (i < count) {
      var x = Math.floor(Math.random() * (750 - 1 + 1)) + 1;;
      var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;
      var z = Math.floor(Math.random() * (75 - 15 + 1)) + 15;

      series.push([x, y, z]);
      baseval += 86400000;
      i++;
    }
    return series;
  }


  // Column chart
  if ($('#sales_chart').length > 0) {
    var columnCtx = document.getElementById("sales_chart"),
      columnConfig = {
        colors: ['#7638ff', '#fda600'],
        series: [
          {
            name: "Received",
            type: "column",
            data: [70, 150, 80, 180, 150, 175, 201, 60, 200, 120, 190, 160, 50]
          },
          {
            name: "Pending",
            type: "column",
            data: [23, 42, 35, 27, 43, 22, 17, 31, 22, 22, 12, 16, 80]
          }
        ],
        chart: {
          type: 'bar',
          fontFamily: 'Poppins, sans-serif',
          height: 350,
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '60%',
            endingShape: 'rounded'
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
        },
        yaxis: {
          title: {
            text: '$ (thousands)'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "$ " + val + " thousands"
            }
          }
        }
      };
    var columnChart = new ApexCharts(columnCtx, columnConfig);
    columnChart.render();
  }


  if ($('#reservation-chart').length > 0) {
    var sCol = {
        chart: {
            width: '100%',
            height: 'auto', // Adjusts dynamically
            type: 'bar',
            toolbar: { show: false },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '80%', // Adjust spacing
                endingShape: 'rounded'
            }
        },
        colors: ['#D0E3E6', '#4361ee'],
        states: {
            hover: {
                filter: {
                    type: 'darken',
                    value: 0.3
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        series: [{
            name: 'Net Profit',
            data: [7, 9, 4, 9, 6, 8, 10]
        }],
        fill: { opacity: 1 },
        xaxis: {
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July'],
            labels: { show: false },
            axisTicks: { show: false },
            axisBorder: { show: false }
        },
        grid: {
            show: false, // Hides grid lines
            padding: { left: 0, right: 0, top: 0, bottom: 0 } 
        },
        yaxis: { labels: { show: false } },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val;
                }
            }
        }
    };

    var chart = new ApexCharts(
        document.querySelector("#reservation-chart"),
        sCol
    );

    chart.render();
}

  //Report Chart
  if ($('#report_chart').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        width: 70,
        height: 46,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#7539FF'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };
    
    
    

    var chart = new ApexCharts(document.querySelector("#report_chart"), options);
    chart.render();
  }
  if ($('#report_chart_2').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        width: 70,
        height: 50,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#27AE60'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };
    
    
    

    var chart = new ApexCharts(document.querySelector("#report_chart_2"), options);
    chart.render();
  }
  if ($('#report_chart_3').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        width: 70,
        height: 50,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#E2B93B'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#report_chart_3"), options);
    chart.render();
  }
  if ($('#report_chart_4').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        width: 70,
        height: 50,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#EF1E1E'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#report_chart_4"), options);
    chart.render();
  }
  //Payment Report Chart
  if ($('#payment_report_chart').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        height: 46,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#7539FF'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };
    
    
    

    var chart = new ApexCharts(document.querySelector("#payment_report_chart"), options);
    chart.render();
  }
  if ($('#payment_report_chart_2').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        height: 50,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#27AE60'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };
    
    
    

    var chart = new ApexCharts(document.querySelector("#payment_report_chart_2"), options);
    chart.render();
  }
  if ($('#payment_report_chart_3').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        height: 50,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#E2B93B'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#payment_report_chart_3"), options);
    chart.render();
  }
  if ($('#payment_report_chart_4').length > 0) {
    var options = {
      series: [{
        data: [0, 6, 24, 14, 20, 15, 37]
      }],
      chart: {
        type: 'area',
        height: 50,
        sparkline: {
          enabled: true
        }
      },
      stroke: {
        curve: 'smooth',
        width: 2 
      },
      colors: ['#EF1E1E'],
    
      tooltip: {
        fixed: {
          enabled: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function (seriesName) {
              return ''
            }
          }
        },
        marker: {
          show: false
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#payment_report_chart_4"), options);
    chart.render();
  }
  //Pie Chart
  if ($('#invoice_chart').length > 0) {
    var pieCtx = document.getElementById("invoice_chart"),
      pieConfig = {
        colors: ['#03C95A', '#E70D0D', '#AB47BC', '#FFC107'],
        series: [45, 15, 21, 5],
        chart: {
          fontFamily: 'Poppins, sans-serif',
          height: 150,
          type: 'donut',
          offsetX: -30,
        },
        labels: ['Paid', 'Overdue', 'Pending', 'Draft'],
        legend: { show: true },
        dataLabels: {
          enabled: false // Disable the data labels
        },
        plotOptions: {
          pie: {
            donut: {
              labels: {
                show: true,
                name: {
                  show: true,
                  fontSize: '2px',
                },
                value: {
                  show: true,
                  fontSize: '12px',
                  formatter: function (val) {
                    return val + "%";
                  }
                },
                total: {
                  show: true,
                  showAlways: true,
                  formatter: function (w) {
                    return w.globals.seriesTotals.reduce((a, b) => {
                      return 45;
                    }, 0);
                  },
                  label: 'Paid'
                }
              }
            }
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            chart: {
              width: 300
            },
            legend: {
              position: 'right'
            }
          }
        }]
      };
    var pieChart = new ApexCharts(pieCtx, pieConfig);
    pieChart.render();
  }


  // Simple Line
  if ($('#s-line').length > 0) {
    var sline = {
      chart: {
        height: 350,
        type: 'line',
        zoom: {
          enabled: false
        },
        toolbar: {
          show: false,
        }
      },
      colors: ['#3550DC'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight'
      },
      series: [{
        name: "Desktops",
        data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
      }],
      title: {
        text: 'Product Trends by Month',
        align: 'left'
      },
      grid: {
        row: {
          colors: ['#f1f2f3', 'transparent'], // takes an array which will be repeated on columns
          opacity: 0.5
        },
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
      }
    }

    var chart = new ApexCharts(
      document.querySelector("#s-line"),
      sline
    );

    chart.render();
  }


  // Simple Line Area
  if ($('#s-line-area').length > 0) {
    var sLineArea = {
      chart: {
        height: 350,
        type: 'area',
        toolbar: {
          show: false,
        }
      },
      colors: ['#3550DC', '#888ea8'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth'
      },
      series: [{
        name: 'series1',
        data: [31, 40, 28, 51, 42, 109, 100]
      }, {
        name: 'series2',
        data: [11, 32, 45, 32, 34, 52, 41]
      }],

      xaxis: {
        type: 'datetime',
        categories: ["2018-09-19T00:00:00", "2018-09-19T01:30:00", "2018-09-19T02:30:00", "2018-09-19T03:30:00", "2018-09-19T04:30:00", "2018-09-19T05:30:00", "2018-09-19T06:30:00"],
      },
      tooltip: {
        x: {
          format: 'dd/MM/yy HH:mm'
        },
      }
    }

    var chart = new ApexCharts(
      document.querySelector("#s-line-area"),
      sLineArea
    );

    chart.render();
  }

  if ($('#s-col').length > 0) {
    var sCol = {
      chart: {
        height: 290,
        type: 'bar',
        toolbar: {
          show: false,
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '80%',
          borderRadius: 5, 
          endingShape: 'rounded', // This rounds the top edges of the bars
        },
      },
      colors: ['#FFAD6A', '#5777E6', '#5CC583'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      
      series: [{
        name: 'Inprogress',
        data: [19, 65, 19, 19, 19, 19, 19]
      }, {
        name: 'Active',
        data: [89, 45, 89, 46, 61, 25, 79]
      }, 
      {
        name: 'Completed',
        data: [39, 39, 39, 80, 48, 48, 48]
      }],
      xaxis: {
        categories: ['15 Jan', '16 Jan', '17 Jan', '18 Jan', '19 Jan', '20 Jan', '21 Jan'],
        labels: {
          style: {
            colors: '#0C1C29', 
            fontSize: '12px',
          }
        }
      },
      yaxis: {
        labels: {
          offsetX: -15,
          style: {
            colors: '#6D777F', 
            fontSize: '14px',
          }
        }
      },
      grid: {
        borderColor: '#CED2D4',
        strokeDashArray: 5,
        padding: {
          left: -8,
          right: -15, 
        },
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return "" + val + "%"
          }
        }
      }
    }
  
    var chart = new ApexCharts(
      document.querySelector("#s-col"),
      sCol
    );
  
    chart.render();
  }

  if ($('#earnings-chart').length > 0) {

    var options = {
      chart: {
        height: 400,
        type: 'bar',
        stacked: true,
        toolbar: { show: false }
      },

      plotOptions: {
        bar: {
          columnWidth: '60%',
          borderRadius: 5,
          borderRadiusApplication: 'end'
        }
      },

      series: [
        {
          name: "Income",
          data: [22,40,27,53,40,27,35,27,43,24,45,56]
        },
        {
          name: "Remaining",
          data: [12,20,13,27,20,13,19,13,22,12,23,29]
        }
      ],

      colors: ['#7539FF', '#E6E4F6'],

      fill: [
        {
          type: "gradient",
          gradient: {
            shade: "dark",
            type: "vertical",
            gradientToColors: ["#7539FF"],
            opacityFrom: 1,
            opacityTo: 1,
            stops: [0,100]
          }
        },
        {
          type: "pattern",
          pattern: {
            style: "verticalLines",
            width: 6,
            height: 6,
            strokeWidth: 2
          }
        }
      ],

      dataLabels: { enabled: false },

      stroke: {
        show: false
      },

      xaxis: {
        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        labels: {
          style: {
            colors: '#051321',
            fontSize: '14px'
          }
        }
      },

      yaxis: {
        max: 100,
        labels: {
          offsetX: -15,
          style: {
            colors: '#051321',
            fontSize: '14px'
          }
        }
      },

      grid: {
        borderColor: '#CED2D4',
        strokeDashArray: 5,
        padding: {
          left: -8,
          right: -10,
          bottom: -10
        }
      },

      legend: { show: false },

      tooltip: {
        marker: { show: false },
        y: {
          formatter: function(val){
            return val + "%";
          }
        }
      }

    };

    var chart = new ApexCharts(
      document.querySelector("#earnings-chart"),
      options
    );

    chart.render();

  }
  
if ($('#register-chart').length > 0) {

  var options = {
    chart: {
      height: 320,
      type: 'area',
      toolbar: {
        show: false
      },
      zoom: {
        enabled: false
      }
    },

    series: [{
      name: 'Companies Registered',
      data: [72, 62, 62, 80, 92, 80, 100]
    }],

    colors: ['#7B4DFF'],

    stroke: {
      curve: 'smooth',
      width: 0
    },

    markers: {
      size: 0,
      strokeWidth: 3,
      strokeColors: "#8A6DFF",
      colors: "#fff",
      hover: {
        size: 7
      }
    },

    fill: {
      type: "gradient",
      gradient: {
        shade: "light",
        type: "vertical",
        shadeIntensity: 0.5,
        gradientToColors: ["#8A6DFF"],
        opacityFrom: 0.8,
        opacityTo: 0.1,
        stops: [0, 100]
      }
    },

    dataLabels: {
      enabled: false
    },

    xaxis: {
      categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      labels: {
        style: {
          colors: '#6B7280',
          fontSize: '14px'
        }
      },
      axisBorder: {
        show: false
      },
      axisTicks: {
        show: false
      },
      crosshairs: {
        show: true,
        stroke: {
          color: '#7B4DFF',
          width: 2,
          dashArray: 0
        }
      }
    },

    yaxis: {
      max: 100,
      min: 0,
      tickAmount: 5,      
      labels: {
        offsetX: -15,
        style: {
          colors: '#6B7280',
          fontSize: '14px'
        }
      }
    },

    grid: {
      borderColor: '#E5E7EB',
      strokeDashArray: 4, 
      padding: {
        left: -10,
        right: -5,
        bottom: -10
      }
    },

    tooltip: {
      marker: {
        show: false
      },
      y: {
        formatter: function(val) {
          return val;
        }
      }
    }
  };

  var chart = new ApexCharts(
    document.querySelector("#register-chart"),
    options
  );

  chart.render();
}
  
  if ($('#plane-chart').length > 0) {
    var options = {
      series: [{
        data: [400, 325, 312, 294, 254, 254]
      }],
      chart: {
        type: 'bar',
        height: 300,
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          barHeight: '100%',
          distributed: true,
          horizontal: true,
          borderRadius: 5,
          borderRadiusApplication: 'end',
          borderWidth: 0
        }
      },
      dataLabels: {
        enabled: true,
        style: {
          fontSize: '14px',
          fontWeight: '500',
          colors: ['#1D1D1D']
        },
        formatter: function (val, opt) {
          // Show label from category with value
          return categories[opt.dataPointIndex] + ": " + val;
          show
        },
        offsetX: 10,
        dropShadow: {
          enabled: false
        }
      },
      grid: {
        padding: {
        left: -10,
        right: 0,
        top: -20,
        bottom: -15
        }
      },
      legend: {
        show: false
      },
      colors: ['#FFECEC', '#DDF3FF', '#EADDFF', '#E1FFED', '#EADFF0', '#FFF8E7'],
      stroke: {
        width: 0,
        colors: ['#1D1D1D'],
      },
      xaxis: {
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      tooltip: {
        marker: {
          show: false
        },
        x: {
          show: false
        },
        y: {
          title: {
            formatter: function () {
              return ''; // Hide the title
            }
          },
          formatter: function (val, opts) {
            return categories[opts.dataPointIndex] + ': ' + val;
          }
        }
      }
    };
  
    // Categories used for labeling inside dataLabels
    const categories = [
      'Enterprise (Monthly) • Sales: $6,100.00',
      'Basic (Yearly) • Sales: $5,100.00',
      'Advanced (Monthly) • Sales: $4,200.00',
      'Enterprise (Yearly) • Sales: $4,100.00',
      'Basic (Monthly) • Sales: $3,100.00',
      'Advanced (Monthly) • Sales: $2,900.00'
    ];
  
    var chart = new ApexCharts(document.querySelector("#plane-chart"), options);
    chart.render();
  }
  
  
  // Simple Column Stacked
  if ($('#s-col-stacked').length > 0) {
    var sColStacked = {
      chart: {
        height: 350,
        type: 'bar',
        stacked: true,
        toolbar: {
          show: false,
        }
      },
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
          }
        }
      }],
      plotOptions: {
        bar: {
          horizontal: false,
        },
      },
      colors: ['#3550DC', '#E70D0D', '#03C95A', '#1B84FF'],
      series: [{
        name: 'PRODUCT A',
        data: [44, 55, 41, 67, 22, 43]
      }, {
        name: 'PRODUCT B',
        data: [13, 23, 20, 8, 13, 27]
      }, {
        name: 'PRODUCT C',
        data: [11, 17, 15, 15, 21, 14]
      }, {
        name: 'PRODUCT D',
        data: [21, 7, 25, 13, 22, 8]
      }],
      xaxis: {
        type: 'datetime',
        categories: ['01/01/2011 GMT', '01/02/2011 GMT', '01/03/2011 GMT', '01/04/2011 GMT', '01/05/2011 GMT', '01/06/2011 GMT'],
      },
      legend: {
        position: 'right',
        offsetY: 40
      },
      fill: {
        opacity: 1
      },
    }

    var chart = new ApexCharts(
      document.querySelector("#s-col-stacked"),
      sColStacked
    );

    chart.render();
  }

  // Simple Bar
  if ($('#s-bar').length > 0) {
    var sBar = {
      chart: {
        height: 350,
        type: 'bar',
        toolbar: {
          show: false,
        }
      },
      colors: ['#3550DC'],
      plotOptions: {
        bar: {
          horizontal: true,
        }
      },
      dataLabels: {
        enabled: false
      },
      series: [{
        data: [400, 430, 448, 470, 540, 580, 690, 1100, 1200, 1380]
      }],
      xaxis: {
        categories: ['South Korea', 'Canada', 'United Kingdom', 'Netherlands', 'Italy', 'France', 'Japan', 'United States', 'China', 'Germany'],
      }
    }

    var chart = new ApexCharts(
      document.querySelector("#s-bar"),
      sBar
    );

    chart.render();
  }

  // Mixed Chart
  if ($('#mixed-chart').length > 0) {
    var options = {
      chart: {
        height: 350,
        type: 'line',
        toolbar: {
          show: false,
        }
      },
      colors: ['#3550DC', '#888ea8'],
      series: [{
        name: 'Website Blog',
        type: 'column',
        data: [440, 505, 414, 671, 227, 413, 201, 352, 752, 320, 257, 160]
      }, {
        name: 'Social Media',
        type: 'line',
        data: [23, 42, 35, 27, 43, 22, 17, 31, 22, 22, 12, 16]
      }],
      stroke: {
        width: [0, 4]
      },
      title: {
        text: 'Traffic Sources'
      },
      labels: ['01 Jan 2001', '02 Jan 2001', '03 Jan 2001', '04 Jan 2001', '05 Jan 2001', '06 Jan 2001', '07 Jan 2001', '08 Jan 2001', '09 Jan 2001', '10 Jan 2001', '11 Jan 2001', '12 Jan 2001'],
      xaxis: {
        type: 'datetime'
      },
      yaxis: [{
        title: {
          text: 'Website Blog',
        },

      }, {
        opposite: true,
        title: {
          text: 'Social Media'
        }
      }]

    }

    var chart = new ApexCharts(
      document.querySelector("#mixed-chart"),
      options
    );

    chart.render();
  }

  // Donut Chart

  if ($('#donut-chart').length > 0) {
    var donutChart = {
      chart: {
        height: 350,
        type: 'donut',
        toolbar: {
          show: false,
        }
      },
      // colors: ['#4361ee', '#888ea8', '#e3e4eb', '#d3d3d3'],
      series: [44, 55, 41, 17],
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    }

    var donut = new ApexCharts(
      document.querySelector("#donut-chart"),
      donutChart
    );

    donut.render();
  }

  // Radial Chart
  if ($('#radial-chart').length > 0) {
    var radialChart = {
      chart: {
        height: 350,
        type: 'radialBar',
        toolbar: {
          show: false,
        }
      },
      // colors: ['#4361ee', '#888ea8', '#e3e4eb', '#d3d3d3'],
      plotOptions: {
        radialBar: {
          dataLabels: {
            name: {
              fontSize: '22px',
            },
            value: {
              fontSize: '16px',
            },
            total: {
              show: true,
              label: 'Total',
              formatter: function (w) {
                return 249
              }
            }
          }
        }
      },
      series: [44, 55, 67, 83],
      labels: ['Apples', 'Oranges', 'Bananas', 'Berries'],
    }

    var chart = new ApexCharts(
      document.querySelector("#radial-chart"),
      radialChart
    );

    chart.render();
  }

   // Radial Chart2
   if ($('#radial-chart2').length > 0) {

  var options = {
    chart: {
      type: 'donut',
      height: 180
    },

    series: [30, 20, 15, 35],

    labels: ['Paid', 'Partially Paid', 'Unpaid', 'Overdue'],

    colors: ['#27AE60', '#7539FF', '#E2B93B', '#DD2590'],

    legend: {
      show: false
    },

    dataLabels: {
      enabled: false
    },

    stroke: {
      width: 6,
      colors: ['#fff']   // white gap between segments
    },

    plotOptions: {
      pie: {
        expandOnClick: false,
        donut: {
          size: '62%',

          labels: {
            show: true,

            name: {
              show: true,
              text: 'Total',
              fontSize: '16px',
              fontWeight: 500,
              offsetY: -10,
              color: '#6B7280'
            },

            value: {
              show: true,
              fontSize: '28px',
              fontWeight: 700,
              offsetY: 10,
              color: '#111827',
              formatter: function () {
                return '$3656';
              }
            }
          }
        }
      }
    },

    tooltip: {
      enabled: false
    }

  };

  var chart = new ApexCharts(
    document.querySelector("#radial-chart2"),
    options
  );

  chart.render();
}
  
   // Radial Chart3
   if ($('#radial-chart3').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#7539FF', '#EFEEFF'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '80%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart3"),
      options
    );
  
    chart.render();
  }

     // Radial Chart4
     if ($('#radial-chart4').length > 0) {
      var options = {
        chart: {
          type: 'donut',
          height: 49,
          width: 49,
        },
        series: [75, 25], // Adjust this for progress percentage
        labels: ['Completed', 'Remaining'],
        colors: ['#27AE60', '#E9F7EF'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: false
        },
        plotOptions: {
          pie: {
            expandOnClick: false,
            donut: {
              size: '80%', // Adjust for better inner circle spacing
              labels: {
                show: true,
                name: {
                  show: false
                },
                value: {
                  show: true,
                  fontSize: '10px', // Small font to fit the size
                  fontWeight: 600,
                  offsetY: 0,
                  color: '#7539FF',
                  formatter: function () {
                    return '75%'; // or any other center label
                  }
                }
              }
            }
          }
        },
        tooltip: {
          enabled: false
        }
      };
    
      var chart = new ApexCharts(
        document.querySelector("#radial-chart4"),
        options
      );
    
      chart.render();
    }


    // Radial Chart5
    if ($('#radial-chart5').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#E2B93B', '#FCF8EB'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '80%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart5"),
      options
    );
  
    chart.render();
  }

  // Radial Chart6
    if ($('#radial-chart6').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#EF1E1E', '#FDE9E9'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '80%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart6"),
      options
    );
  
    chart.render();
  }

  // Radial Chart7
  if ($('#radial-chart7').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#27AE60', '#EFEEFF'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '60%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart7"),
      options
    );
  
    chart.render();
  }

  // Radial Chart8
  if ($('#radial-chart8').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#E2B93B', '#EFEEFF'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '60%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart8"),
      options
    );
  
    chart.render();
  }

  // Radial Chart9
  if ($('#radial-chart9').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#EF1E1E', '#EFEEFF'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '60%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart9"),
      options
    );
  
    chart.render();
  }

  // Radial Chart10
  if ($('#radial-chart10').length > 0) {
    var options = {
      chart: {
        type: 'donut',
        height: 49,
        width: 49,
      },
      series: [75, 25], // Adjust this for progress percentage
      labels: ['Completed', 'Remaining'],
      colors: ['#7539FF', '#EFEEFF'],
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: false
      },
      plotOptions: {
        pie: {
          expandOnClick: false,
          donut: {
            size: '60%', // Adjust for better inner circle spacing
            labels: {
              show: true,
              name: {
                show: false
              },
              value: {
                show: true,
                fontSize: '10px', // Small font to fit the size
                fontWeight: 600,
                offsetY: 0,
                color: '#7539FF',
                formatter: function () {
                  return '75%'; // or any other center label
                }
              }
            }
          }
        }
      },
      tooltip: {
        enabled: false
      }
    };
  
    var chart = new ApexCharts(
      document.querySelector("#radial-chart10"),
      options
    );
  
    chart.render();
  }

    // Radial Chart11
    if ($('#radial-chart11').length > 0) {
      var options = {
        chart: {
          type: 'donut',
          height: 49,
          width: 49,
        },
        series: [75, 25], // Adjust this for progress percentage
        labels: ['Completed', 'Remaining'],
        colors: ['#7539FF', '#EFEEFF'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: false
        },
        plotOptions: {
          pie: {
            expandOnClick: false,
            donut: {
              size: '60%', // Adjust for better inner circle spacing
              labels: {
                show: true,
                name: {
                  show: false
                },
                value: {
                  show: true,
                  fontSize: '10px', // Small font to fit the size
                  fontWeight: 600,
                  offsetY: 0,
                  color: '#7539FF',
                  formatter: function () {
                    return '75%'; // or any other center label
                  }
                }
              }
            }
          }
        },
        tooltip: {
          enabled: false
        }
      };
    
      var chart = new ApexCharts(
        document.querySelector("#radial-chart11"),
        options
      );
    
      chart.render();
    }
  
    // Radial Chart12
    if ($('#radial-chart12').length > 0) {
      var options = {
        chart: {
          type: 'donut',
          height: 49,
          width: 49,
        },
        series: [75, 25], // Adjust this for progress percentage
        labels: ['Completed', 'Remaining'],
        colors: ['#27AE60', '#EFEEFF'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: false
        },
        plotOptions: {
          pie: {
            expandOnClick: false,
            donut: {
              size: '60%', // Adjust for better inner circle spacing
              labels: {
                show: true,
                name: {
                  show: false
                },
                value: {
                  show: true,
                  fontSize: '10px', // Small font to fit the size
                  fontWeight: 600,
                  offsetY: 0,
                  color: '#7539FF',
                  formatter: function () {
                    return '75%'; // or any other center label
                  }
                }
              }
            }
          }
        },
        tooltip: {
          enabled: false
        }
      };
    
      var chart = new ApexCharts(
        document.querySelector("#radial-chart12"),
        options
      );
    
      chart.render();
    }
  
    // Radial Chart13
    if ($('#radial-chart13').length > 0) {
      var options = {
        chart: {
          type: 'donut',
          height: 49,
          width: 49,
        },
        series: [75, 25], // Adjust this for progress percentage
        labels: ['Completed', 'Remaining'],
        colors: ['#E2B93B', '#EFEEFF'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: false
        },
        plotOptions: {
          pie: {
            expandOnClick: false,
            donut: {
              size: '60%', // Adjust for better inner circle spacing
              labels: {
                show: true,
                name: {
                  show: false
                },
                value: {
                  show: true,
                  fontSize: '10px', // Small font to fit the size
                  fontWeight: 600,
                  offsetY: 0,
                  color: '#7539FF',
                  formatter: function () {
                    return '75%'; // or any other center label
                  }
                }
              }
            }
          }
        },
        tooltip: {
          enabled: false
        }
      };
    
      var chart = new ApexCharts(
        document.querySelector("#radial-chart13"),
        options
      );
    
      chart.render();
    }
  
    // Radial Chart14
    if ($('#radial-chart14').length > 0) {
      var options = {
        chart: {
          type: 'donut',
          height: 49,
          width: 49,
        },
        series: [75, 25], // Adjust this for progress percentage
        labels: ['Completed', 'Remaining'],
        colors: ['#EF1E1E', '#EFEEFF'],
        legend: {
          show: false
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: false
        },
        plotOptions: {
          pie: {
            expandOnClick: false,
            donut: {
              size: '60%', // Adjust for better inner circle spacing
              labels: {
                show: true,
                name: {
                  show: false
                },
                value: {
                  show: true,
                  fontSize: '10px', // Small font to fit the size
                  fontWeight: 600,
                  offsetY: 0,
                  color: '#7539FF',
                  formatter: function () {
                    return '75%'; // or any other center label
                  }
                }
              }
            }
          }
        },
        tooltip: {
          enabled: false
        }
      };
    
      var chart = new ApexCharts(
        document.querySelector("#radial-chart14"),
        options
      );
    
      chart.render();
    }
  
  
  // end chart

  if ($('#sales_charts').length > 0) {
    var options = {
      series: [{
        name: 'Sales',
        data: [130, 210, 300, 290, 150, 50, 210, 280, 105],
      }, {
        name: 'Purchase',
        data: [-150, -90, -50, -180, -50, -70, -100, -90, -105]
      }],
      colors: ['#28C76F', '#EA5455'],
      chart: {
        type: 'bar',
        height: 320,
        stacked: true,

        zoom: {
          enabled: true
        }
      },
      responsive: [{
        breakpoint: 280,
        options: {
          legend: {
            position: 'bottom',
            offsetY: 0
          }
        }
      }],
      plotOptions: {
        bar: {
          horizontal: false,
          borderRadius: 4,
          borderRadiusApplication: "end", // "around" / "end" 
          borderRadiusWhenStacked: "all", // "all"/"last"
          columnWidth: '20%',
        },
      },
      dataLabels: {
        enabled: false
      },
      yaxis: {
        min: -200,
        max: 300,
        tickAmount: 5,
      },
      xaxis: {
        categories: [' Jan ', 'Feb', 'Mar', 'Apr',
          'May', 'Jun', 'Jul', 'Aug', 'Sep'
        ],
      },
      legend: { show: false },
      fill: {
        opacity: 1
      }
    };

    var chart = new ApexCharts(document.querySelector("#sales_charts"), options);
    chart.render();
  }

  if ($('#sales-analysis').length > 0) {
    var options = {
      series: [{
        name: "Sales Analysis",
        data: [25, 30, 18, 15, 22, 20, 30, 20, 22, 18, 15, 20]
      }],
      chart: {
        height: 273,
        type: 'area',
        zoom: {
          enabled: false
        }
      },
      colors: ['#FF9F43'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight'
      },
      title: {
        text: '',
        align: 'left'
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
      },
      yaxis: {
        min: 10,
        max: 60,
        tickAmount: 5,
        labels: {
          formatter: (val) => {
            return val / 1 + 'K'
          }
        }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'left'
      }
    };

    var chart = new ApexCharts(document.querySelector("#sales-analysis"), options);
    chart.render();
  }

  // Student Chart

  if ($('#teacher-chart').length > 0) {
    var donutChart = {
      chart: {
        height: 260,
        type: 'donut',
        toolbar: {
          show: false,
        }
      },
      colors: ['#3D5EE1', '#6FCCD8'],
      series: [346, 54],
      labels: ['Present', 'Absent'],
      legend: { show: false },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            height: 180,
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    }

    var donut = new ApexCharts(
      document.querySelector("#teacher-chart"),
      donutChart
    );

    donut.render();
  }


  // Student Chart

  if ($('#staff-chart').length > 0) {
    var donutChart = {
      chart: {
        height: 260,
        type: 'donut',
        toolbar: {
          show: false,
        }
      },
      colors: ['#3D5EE1', '#6FCCD8'],
      series: [620, 80],
      labels: ['Present', 'Absent'],
      legend: { show: false },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            height: 180,
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    }

    var donut = new ApexCharts(
      document.querySelector("#staff-chart"),
      donutChart
    );

    donut.render();
  }


  // Class Chart

  if ($('#class-chart').length > 0) {
    var donutChart = {
      chart: {
        height: 130,
        type: 'donut',
        toolbar: {
          show: false,
        },
        sparkline: {
          enabled: true
        }
      },
      colors: ['#3D5EE1', '#EAB300', '#E82646'],
      series: [45, 11, 2],
      labels: ['Good', 'Average', 'Below Average'],
      legend: { show: false },
      dataLabels: {
        enabled: false
      },
      yaxis: {
        tickAmount: 3,
        labels: {
          offsetX: -15,
        },
      },
      grid: {
        padding: {
          left: -8,
        },
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
    }

    var donut = new ApexCharts(
      document.querySelector("#class-chart"),
      donutChart
    );

    donut.render();
  }

  // Leaves Chart

  if ($('#web_chart').length > 0) {
    var donutChart = {
      chart: {
        height: 205,
        type: 'donut',
        toolbar: {
          show: false,
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '30%',
        },
      },
      dataLabels: {
        enabled: false
      },
      series: [41, 11, 7, 18, 6, 12, 4, 16],
      colors: ['#FF7F00', '#FF0000', '#8000FF', '#27EAEA', '#01B664', '#F9B801', '#24CDBA', '#AB47BC'],
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 50,
          },
          legend: {
            show: false
          }
        }
      }],
      legend: {
        show: false
      }
    }

    var donut = new ApexCharts(
      document.querySelector("#web_chart"),
      donutChart
    );

    donut.render();
  }

  // Fees Chart

  if ($('#fees-chart').length > 0) {
    var sCol = {
      chart: {
        height: 275,
        type: 'bar',
        stacked: true,
        toolbar: {
          show: false,
        }
      },
      legend: {
        show: true,
        horizontalAlign: 'left',
        position: 'top',
        fontSize: '14px',
        labels: {
          colors: '#5D6369',
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '50%',
          endingShape: 'rounded'
        },
      },
      colors: ['#3D5EE1', '#E9EDF4'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      grid: {
        padding: {
          left: -8,
        },
      },
      series: [{
        name: 'Collected Fee',
        data: [30, 40, 38, 40, 38, 30, 35, 38, 40]
      }, {
        name: 'Total Fee',
        data: [45, 50, 48, 50, 48, 40, 40, 50, 55]
      }],
      xaxis: {
        categories: ['Q1: 2023', 'Q1: 2023', 'Q1: 2023', 'Q1: 2023', 'Q1: 2023', 'uQ1: 2023l', 'Q1: 2023', 'Q1: 2023', 'Q1: 2023'],
      },
      yaxis: {
      },
      yaxis: {
        tickAmount: 3,
        labels: {
          offsetX: -15
        },
      },
      fill: {
        opacity: 1

      },
      tooltip: {
        y: {
          formatter: function (val) {
            return "$ " + val + " thousands"
          }
        }
      }
    }

    var chart = new ApexCharts(
      document.querySelector("#fees-chart"),
      sCol
    );

    chart.render();
  }

  if ($('#exam-result-chart').length > 0) {
    var options = {
      chart: {
        type: 'bar',
        height: 310
      },
      series: [{
        name: 'Marks',
        data: [100, 92, 90, 82, 90] // Corresponding scores for Maths, Physics, Chemistry, English, Spanish
      }],
      xaxis: {
        categories: ['Mat', 'Phy', 'Che', 'Eng', 'Sci']
      },
      plotOptions: {
        bar: {
          distributed: true,
          columnWidth: '50%',
          colors: {
            backgroundBarColors: ['#E9EDF4', '#fff'],
            backgroundBarOpacity: 1,
            backgroundBarRadius: 5,
          },
          dataLabels: {
            position: 'top'
          },
        }
      },
      colors: ['#E9EDF4', '#3D5EE1', '#E9EDF4', '#E9EDF4', '#E9EDF4'], // Set specific colors for each bar
      tooltip: {
        y: {
          formatter: function (val) {
            return val + "%"
          }
        }
      },
      dataLabels: {
        enabled: true,
        formatter: function (val) {
          return val + "%";
        },
        offsetY: -20,
        style: {
          fontSize: '14px',
          colors: ["#304758"]
        }
      },
      grid: {
        yaxis: {
          lines: {
            show: false
          }
        },
      },

      legend: {
        show: false
      }
    }

    var chart = new ApexCharts(document.querySelector("#exam-result-chart"), options);
    chart.render();
  }

  if ($('#performance_chart').length > 0) {
    var options = {
      chart: {
        type: 'area',
        height: 355
      },
      series: [{
        name: 'Avg. Exam Score',
        data: [75, 68, 65, 68, 75] // Sample data
      }, {
        name: 'Avg. Attendance',
        data: [85, 78, 75, 78, 85] // Sample data
      }],
      xaxis: {
        categories: ['Quarter 1', 'Quarter 2', 'Half yearly', 'Model', 'Final']
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val + "%";
          }
        },
        shared: true,
        intersect: false,
        custom: function ({ series, seriesIndex, dataPointIndex, w }) {
          return `<div class="apexcharts-tooltip">${w.globals.labels[dataPointIndex]}<br>Exam Score: <span style="color: #1E90FF;">${series[0][dataPointIndex]}%</span><br>Attendance: <span style="color: #00BFFF;">${series[1][dataPointIndex]}%</span></div>`;
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth'
      },
      grid: {
        padding: {
          left: -15,
          right: 0,
        },
      },
      grid: {
        yaxis: {
          axisTicks: {
            show: true,
            borderType: 'solid',
            color: '#78909C',
            width: 6,
            offsetX: 0,
            offsetY: 0
          },

        },
      },
      yaxis: {
        labels: {
          offsetX: -15
        },
      },
      markers: {
        size: 5,
        colors: ['#1E90FF', '#00BFFF'],
        strokeColors: '#fff',
        strokeWidth: 2,
        hover: {
          size: 7
        }
      },
      colors: ['#3D5EE1', '#6FCCD8'], // Color for the lines
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.9,
          stops: [0, 90, 100]
        }
      },
      legend: {
        position: 'bottom',
        horizontalAlign: 'center'
      }
    }
    var chart = new ApexCharts(document.querySelector("#performance_chart"), options);
    chart.render();
  }

  // Plan Chart

  if ($('#plan_chart').length > 0) {
    var donutChart = {
      chart: {
        height: 90,
        type: 'donut',
        toolbar: {
          show: false,
        },
        sparkline: {
          enabled: true
        }
      },
      grid: {
        show: false,
        padding: {
          left: 0,
          right: 0
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '50%'
        },
      },
      dataLabels: {
        enabled: false
      },

      series: [95, 5],
      labels: [
        'Completed',
        'Pending'

      ],
      legend: { show: false },
      colors: ['#3D5EE1', '#E82646'],
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 100
          },
          legend: {
            position: 'bottom'
          }
        }
      }],
      legend: {
        position: 'bottom'
      }
    }

    var donut = new ApexCharts(
      document.querySelector("#plan_chart"),
      donutChart
    );

    donut.render();
  }

  if ($('#statistic_chart').length > 0) {
    var options = {
      chart: {
        type: 'line',
        height: 345,
      },
      series: [{
        name: 'Avg. Exam Score',
        data: [0, 32, 40, 50, 60, 52, 50, 44, 40, 60, 75, 70] // Sample data
      }, {
        name: 'Avg. Attendance',
        data: [0, 35, 43, 34, 30, 28, 25, 50, 60, 75, 77, 80] // Sample data
      }],
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val + "%";
          }
        },
        shared: true,
        intersect: false,
        custom: function ({ series, seriesIndex, dataPointIndex, w }) {
          return `<div class="apexcharts-tooltip">${w.globals.labels[dataPointIndex]}<br>Exam Score: <span style="color: #1E90FF;">${series[0][dataPointIndex]}%</span><br>Attendance: <span style="color: #00BFFF;">${series[1][dataPointIndex]}%</span></div>`;
        }
      },
      dataLabels: {
        enabled: false
      },
      grid: {
        yaxis: {
          lines: {
            show: true
          }
        },
      },
      yaxis: {
        labels: {
          offsetX: -15
        },
      },
      grid: {
        padding: {
          left: -8,
        },
      },
      markers: {
        size: 0,
        colors: ['#1E90FF', '#00BFFF'],
        strokeColors: '#fff',
        strokeWidth: 1,
        hover: {
          size: 7
        }
      },
      colors: ['#3D5EE1', '#6FCCD8'], // Color for the lines
      legend: {
        position: 'top',
        horizontalAlign: 'left'
      }
    }
    var chart = new ApexCharts(document.querySelector("#statistic_chart"), options);
    chart.render();
  }

  if ($('#attendance_chart2').length > 0) {
    var donutChart = {
      chart: {
        height: 290,
        type: 'donut',
        toolbar: {
          show: false,
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '50%'
        },
      },
      dataLabels: {
        enabled: false
      },

      series: [60, 5, 15, 20],
      labels: [
        'Present',
        'Late',
        'Half Day',
        'Absent'
      ],
      colors: ['#1ABE17', '#1170E4', '#E9EDF4', '#E82646'],
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'left'
          }
        }
      }],
      legend: {
        position: 'left',
      }
    }

    var donut = new ApexCharts(
      document.querySelector("#attendance_chart2"),
      donutChart
    );

    donut.render();
  }

  // Total Earning
  if ($('#total-earning').length > 0) {
    var sLineArea = {
      chart: {
        height: 90,
        type: 'area',
        toolbar: {
          show: false,
        },
        sparkline: {
          enabled: true
        }
      },
      colors: ['#3D5EE1'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight'
      },
      series: [{
        name: 'Earnings',
        data: [50, 60, 40, 50, 45, 55, 50]
      }]
    }

    var chart = new ApexCharts(
      document.querySelector("#total-earning"),
      sLineArea
    );

    chart.render();
  }

  // Total Expenses
  if ($('#total-expenses').length > 0) {
    var sLineArea = {
      chart: {
        height: 90,
        type: 'area',
        toolbar: {
          show: false,
        },
        sparkline: {
          enabled: true
        }
      },
      colors: ['#E82646'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight'
      },
      series: [{
        name: 'Earnings',
        data: [40, 20, 60, 55, 50, 55, 40]
      }]
    }

    var chart = new ApexCharts(
      document.querySelector("#total-expenses"),
      sLineArea
    );

    chart.render();
  }

});

// Donut Chart

if ($('#storage-chart').length > 0) {
  var donutChart = {
    chart: {
      height: 200,
      type: 'donut',
      toolbar: {
        show: false,
      },
      offsetY: -10,
      events: {
        rendered: function () {
          // Adding the center text
          var chartElement = document.querySelector("#donutChart");
          var innerText = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">' +
            '<span style="font-size: 24px; font-weight: bold;">Total</span><br>' +
            '<span style="font-size: 16px;">abb</span>' +
            '</div>';
          chartElement.innerHTML += innerText;
        }
      },
    },
    plotOptions: {
      pie: {
        startAngle: -100,
        endAngle: 100,
        donut: {
          size: '80%',
          labels: {
            show: true,
            name: {
              show: true,
            }
          }
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false
    },
    stroke: {
      show: false
    },
    colors: ['#0C4B5E', '#FFC107', '#1B84FF', '#AB47BC', '#FD3995'],
    series: [20, 20, 20, 20, 20],
    labels: ['Documents', 'Video', 'Music', 'Photos', 'Other'],
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          position: 'bottom'
        }
      }
    }],
    grid: {
      padding: {
        bottom: -60  // Reduce padding from the bottom
      }
    }
  }

  var donut = new ApexCharts(
    document.querySelector("#storage-chart"),
    donutChart
  );

  donut.render();
}

// Total Company

if ($('#work-chart').length > 0) {

  var options = {
    series: [{
      name: "Messages",
      data: [25, 66, 41, 12, 36, 9, 21]
    }],
    fill: {
      type: 'gradient',
      gradient: {
        opacityFrom: 0, // Start with 0 opacity (transparent)
        opacityTo: 0    // End with 0 opacity (transparent)
      }
    },
    chart: {
      foreColor: '#fff',
      type: "area",
      width: 50,
      toolbar: {
        show: !1
      },
      zoom: {
        enabled: !1
      },
      dropShadow: {
        enabled: 0,
        top: 3,
        left: 14,
        blur: 4,
        opacity: .12,
        color: "#fff"
      },
      sparkline: {
        enabled: !0
      }
    },
    markers: {
      size: 0,
      colors: ["#F26522"],
      strokeColors: "#fff",
      strokeWidth: 2,
      hover: {
        size: 7
      }
    },
    plotOptions: {
      bar: {
        horizontal: !1,
        columnWidth: "35%",
        endingShape: "rounded"
      }
    },
    dataLabels: {
      enabled: !1
    },
    stroke: {
      show: !0,
      width: 2.5,
      curve: "smooth"
    },
    colors: ["#F26522"],
    xaxis: {
      categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"]
    },
    tooltip: {
      theme: "dark",
      fixed: {
        enabled: !1
      },
      x: {
        show: !1
      },
      y: {
        title: {
          formatter: function (e) {
            return ""
          }
        }
      },
      marker: {
        show: !1
      }
    }
  };

  var chart = new ApexCharts(document.querySelector("#work-chart"), options);
  chart.render();
}

// Active Company

if ($('#productive-chart').length > 0) {

  var options = {
    series: [{
      name: "Active Company",
      data: [25, 40, 35, 20, 36, 9, 21]
    }],
    fill: {
      type: 'gradient',
      gradient: {
        opacityFrom: 0, // Start with 0 opacity (transparent)
        opacityTo: 0    // End with 0 opacity (transparent)
      }
    },
    chart: {
      foreColor: '#fff',
      type: "area",
      width: 50,
      toolbar: {
        show: !1
      },
      zoom: {
        enabled: !1
      },
      dropShadow: {
        enabled: 0,
        top: 3,
        left: 14,
        blur: 4,
        opacity: .12,
        color: "#fff"
      },
      sparkline: {
        enabled: !0
      }
    },
    markers: {
      size: 0,
      colors: ["#F26522"],
      strokeColors: "#fff",
      strokeWidth: 2,
      hover: {
        size: 7
      }
    },
    plotOptions: {
      bar: {
        horizontal: !1,
        columnWidth: "35%",
        endingShape: "rounded"
      }
    },
    dataLabels: {
      enabled: !1
    },
    stroke: {
      show: !0,
      width: 2.5,
      curve: "smooth"
    },
    colors: ["#F26522"],
    xaxis: {
      categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"]
    },
    tooltip: {
      theme: "dark",
      fixed: {
        enabled: !1
      },
      x: {
        show: !1
      },
      y: {
        title: {
          formatter: function (e) {
            return ""
          }
        }
      },
      marker: {
        show: !1
      }
    }
  };

  var chart = new ApexCharts(document.querySelector("#productive-chart"), options);
  chart.render();
}

// Inactive Company

if ($('#unproductive-chart').length > 0) {

  var options = {
    series: [{
      name: "Inactive Company",
      data: [25, 10, 35, 5, 25, 28, 21]
    }],
    fill: {
      type: 'gradient',
      gradient: {
        opacityFrom: 0, // Start with 0 opacity (transparent)
        opacityTo: 0    // End with 0 opacity (transparent)
      }
    },
    chart: {
      foreColor: '#fff',
      type: "area",
      width: 50,
      toolbar: {
        show: !1
      },
      zoom: {
        enabled: !1
      },
      dropShadow: {
        enabled: 0,
        top: 3,
        left: 14,
        blur: 4,
        opacity: .12,
        color: "#fff"
      },
      sparkline: {
        enabled: !0
      }
    },
    markers: {
      size: 0,
      colors: ["#F26522"],
      strokeColors: "#fff",
      strokeWidth: 2,
      hover: {
        size: 7
      }
    },
    plotOptions: {
      bar: {
        horizontal: !1,
        columnWidth: "35%",
        endingShape: "rounded"
      }
    },
    dataLabels: {
      enabled: !1
    },
    stroke: {
      show: !0,
      width: 2.5,
      curve: "smooth"
    },
    colors: ["#F26522"],
    xaxis: {
      categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"]
    },
    tooltip: {
      theme: "dark",
      fixed: {
        enabled: !1
      },
      x: {
        show: !1
      },
      y: {
        title: {
          formatter: function (e) {
            return ""
          }
        }
      },
      marker: {
        show: !1
      }
    }
  };

  var chart = new ApexCharts(document.querySelector("#unproductive-chart"), options);
  chart.render();
}

// Location Company

if ($('#utilization-chart').length > 0) {

  var options = {
    series: [{
      name: "Inactive Company",
      data: [30, 40, 15, 23, 20, 23, 25]
    }],
    fill: {
      type: 'gradient',
      gradient: {
        opacityFrom: 0, // Start with 0 opacity (transparent)
        opacityTo: 0    // End with 0 opacity (transparent)
      }
    },
    chart: {
      foreColor: '#fff',
      type: "area",
      width: 50,
      toolbar: {
        show: !1
      },
      zoom: {
        enabled: !1
      },
      dropShadow: {
        enabled: 0,
        top: 3,
        left: 14,
        blur: 4,
        opacity: .12,
        color: "#fff"
      },
      sparkline: {
        enabled: !0
      }
    },
    markers: {
      size: 0,
      colors: ["#F26522"],
      strokeColors: "#fff",
      strokeWidth: 2,
      hover: {
        size: 7
      }
    },
    plotOptions: {
      bar: {
        horizontal: !1,
        columnWidth: "35%",
        endingShape: "rounded"
      }
    },
    dataLabels: {
      enabled: !1
    },
    stroke: {
      show: !0,
      width: 2.5,
      curve: "smooth"
    },
    colors: ["#F26522"],
    xaxis: {
      categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep"]
    },
    tooltip: {
      theme: "dark",
      fixed: {
        enabled: !1
      },
      x: {
        show: !1
      },
      y: {
        title: {
          formatter: function (e) {
            return ""
          }
        }
      },
      marker: {
        show: !1
      }
    }
  };

  var chart = new ApexCharts(document.querySelector("#utilization-chart"), options);
  chart.render();
}

if ($('#expense-analysis').length > 0) {
  var options = {
    series: [{
      name: "Sales Analysis",
      data: [10, 30, 18, 15, 22, 30, 40, 50, 40, 40, 60, 70]
    }],
    chart: {
      height: 190,
      type: 'area',
      zoom: {
        enabled: false
      }
    },
    colors: ['#FF9F43'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth'
    },
    title: {
      text: '',
      align: 'left'
    },
    // grid: {
    //   row: {
    //     colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
    //     opacity: 0.5
    //   },
    // },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
    },
    yaxis: {
      min: 10,
      max: 60,
      tickAmount: 5,
      labels: {
        offsetX: -15,
        formatter: (val) => {
          return val / 1 + 'K'
        }
      }
    },
    legend: {
      position: 'top',
      horizontalAlign: 'left'
    }
  };

  var chart = new ApexCharts(document.querySelector("#expense-analysis"), options);
  chart.render();
}

if ($('#invoice-report').length > 0) {
  var options = {
    series: [{
      name: 'Total Invoices',
      data: [40, 30, 40, 30, 40, 30]
    }, {
      name: 'Paid Invoices',
      data: [20, 10, 20, 10, 20, 10]
    }],
    chart: {
      height: 250,
      type: 'area'
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'straight'
    },
    xaxis: {
      type: 'category',
      categories: ["January", "February", "March", "April", "May", "June", "July"]
    },
    yaxis: {
      labels: {
        offsetX: -15,
        formatter: function (value) {
          return value + "k"; // Display values with 'k' suffix
        }
      },
    },
    tooltip: {
      x: {
        formatter: function (value) {
          return value; // Tooltip shows month labels
        }
      },
      y: {
        formatter: function (value) {
          return value + "k"; // Tooltip shows amounts with 'k' suffix
        }
      }
    },
    colors: ['#FD3995','#FF9F43'],
    dataLabels: {
      enabled: false
    },
  };

  // Create the chart instance
  var chart = new ApexCharts(document.querySelector("#invoice-report"), options);
  chart.render();
}

if($('#payment-report').length > 0 ){
  var options = {
    series: [44, 55, 41, 17],
    chart: {
      type: 'donut',
    },
    colors: ['#0DCAF0', '#FD3995', '#AB47BC', '#FFC107'],
    labels: ['Paypal', 'Debit Card', 'Bank Transfer', 'Credit Card'],
    plotOptions: {
      pie: {
        startAngle: -90,
        endAngle: 270,
        stroke: {
          show: true,
          width: 10, // Width of the gap
          colors: ['#FFFFFF'] // Color of the gap
        },
        donut: {
          size: '80%' // Adjusts the size of the donut hole
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false // Set this to false to hide the legend
    },
    annotations: {
      position: 'front', // Ensure it appears above other elements
      style: {
        fontSize: '24px', // Adjust font size
        fontWeight: 'bold',
        color: '#000000' // Change color if needed
      },
      text: {
        // Set the annotation text
        text: '+14%',
        // Optional styling for the text box
        background: {
          enabled: true,
          foreColor: '#FFFFFF', // Text color
          border: '#000000', // Border color
          borderWidth: 1,
          borderRadius: 2,
          opacity: 0.7
        }
      },
      x: '50%', // Center horizontally
      y: '50%', // Center vertically
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          show: false // Also hide legend on smaller screens
        }
      }
    }]
  };

  var chart = new ApexCharts(document.querySelector("#payment-report"), options);
  chart.render();
}

if($('#task-reports').length > 0 ){
  var options = {
    series: [40, 30, 20, 10],
    chart: {
      type: 'donut',
      width: 220,
    },
    colors: ['#03C95A', '#0DCAF0', '#FFC107', '#AB47BC'],
    labels: ['Completed ', 'Pending', 'Inprogress ', 'On Hold '],
    plotOptions: {
      pie: {
        startAngle: -90,
        endAngle: 270,
        stroke: {
          show: true,
          width: 10, // Width of the gap
          colors: ['#FFFFFF'] // Color of the gap
        },
        donut: {
          size: '80%' // Adjusts the size of the donut hole
        }
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false // Set this to false to hide the legend
    },
    annotations: {
      position: 'front', // Ensure it appears above other elements
      style: {
        fontSize: '24px', // Adjust font size
        fontWeight: 'bold',
        color: '#000000' // Change color if needed
      },
      text: {
        // Set the annotation text
        text: '+14%',
        // Optional styling for the text box
        background: {
          enabled: true,
          foreColor: '#FFFFFF', // Text color
          border: '#000000', // Border color
          borderWidth: 1,
          borderRadius: 2,
          opacity: 0.7
        }
      },
      x: '50%', // Center horizontally
      y: '50%', // Center vertically
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          show: false // Also hide legend on smaller screens
        }
      }
    }]
  };

  var chart = new ApexCharts(document.querySelector("#task-reports"), options);
  chart.render();
}


if($('#project-report').length > 0 ){
  var options = {
    series: [30, 10, 20, 40],
    chart: {
      width: 280,
      type: 'pie',
    },
    labels: ['Pending', 'On Hold', 'In Progress', 'Completed'], // Set your labels here
    colors: ['#0DCAF0', '#AB47BC', '#FFC107', '#03C95A'], // Custom colors for each segment
    dataLabels: {
      enabled: false // Disable data labels to remove numbers
    },
    legend: {
      show: false // Hide the legend
    },
    tooltip: {
      y: {
        formatter: function(value, { seriesIndex }) {
          return 'Value: ' + value; // Customize the tooltip text
        }
      }
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          position: 'bottom'
        }
      }
    }]
  };

  var chart = new ApexCharts(document.querySelector("#project-report"), options);
  chart.render();
}

if ($('#employee-reports').length > 0) {
  var options = {
    series: [{
      name: 'Active Employees',
      data: [50, 55, 57, 56, 61, 58, 63, 60, 66]
    }, {
      name: 'Inactive Employees',
      data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
    }],
    chart: {
      type: 'bar',
      height: 180
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '55%',
        endingShape: 'rounded'
      }
    },
    colors: ['#03C95A', '#E8E9EA'], // Active Employees - Green, Inactive Employees - Gray
    dataLabels: {
      enabled: false, // Disable data labels
    },
    stroke: {
      show: true,
      width: 2,
      colors: ['transparent']
    },
    xaxis: {
      categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct']
    },yaxis: {
      labels: {
        offsetX: -15,
      }
    },
    fill: {
      opacity: 1
    },
    legend: {
      show: false
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return "$ " + val + " thousands";
        }
      }
    }
  };

  var chart = new ApexCharts(document.querySelector("#employee-reports"), options);
  chart.render();
}

if ($('#attendance-report').length > 0) {
  var options = {
      series: [{
          name: "Present",
          data: [30, 65, 70, 75, 80, 95, 100, 70, 65] // Example data for Present
      }, {
          name: "Absent",
          data: [30, 55, 60, 65, 50, 70, 80, 60, 70] // Example data for Absent
      }],
      chart: {
          height: 200, // Change height here
          type: 'line',
          zoom: {
              enabled: false
          }
      },
      dataLabels: {
          enabled: false
      },
      stroke: {
          curve: 'smooth' // Change to 'smooth' for a nicer appearance
      },
      grid: {
          row: {
              colors: ['#f3f3f3', 'transparent'], // alternating row colors
              opacity: 0.5
          },
      },
      xaxis: {
          categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
      },yaxis: {
        labels: {
          offsetX: -15,
        }
      },
      colors: ['#28a745', '#ff69b4'] // Green for Present, Pink for Absent
  };

  var chart = new ApexCharts(document.querySelector("#attendance-report"), options);
  chart.render();
}

if ($('#leave-report').length > 0) {
  var options = {
    series: [{
      name: 'Annual Leave',
      data: [30, 40, 35, 50,50,60,30, 40, 35, 50,50,60] // Replace with your data
    }, {
      name: 'Casual Leave',
      data: [20, 30, 25, 40,50,60,20, 30, 25, 40,50,60] // Replace with your data
    }, {
      name: 'Medical Leave',
      data: [15, 10, 20, 15,50,60,15, 10, 20, 15,50,60] // Replace with your data
    }, {
      name: 'Others',
      data: [25, 20, 30, 35,50,60,25, 20, 30, 35,50,60] // Replace with your data
    }, 
  ],
    chart: {
      type: 'bar',
      height: 210, // Change this value to your desired height
      stacked: true,
      stackType: '100%'
    },
    responsive: [{
      breakpoint: 480,
      options: {
        legend: {
          position: 'bottom',
          offsetX: -10,
          offsetY: 0
        }
      }
    }],
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec',] // Update to match your time frame
    },
    yaxis: {
      labels: {
        offsetX: -15,
      }
    },
    fill: {
      opacity: 1
    },
    legend: {
     show: false
    },
    colors: ['#03C95A', '#FFC107', '#0C4B5E', '#F26522'], // Set your colors here
    dataLabels: {
      enabled: false // Disable data labels
    }
  };

  var chart = new ApexCharts(document.querySelector("#leave-report"), options);
  chart.render();
}

if ($('#daily-report').length > 0) {
  var options = {
    series: [{
      name: "Present",
      data: [60, 40, 30, 20, 70, ] // Sample data for Present
    }, {
      name: "Absent",
      data: [20, 60, 45, 60, 80,] // Sample data for Absent
    }],
    chart: {
      height: 130, // Changed height
      type: 'line',
      zoom: {
        enabled: false
      }
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false
     },
    stroke: {
      curve: 'smooth' // You can change this to 'straight' if preferred
    },
    grid: {
      row: {
        colors: ['#f3f3f3', 'transparent'],
        opacity: 0.5
      }
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
    },
    yaxis: {
      labels: {
        offsetX: -15,
      }
    },
    colors: ['#4CAF50', '#F44336'] // Green for Present, Red for Absent
  };

  var chart = new ApexCharts(document.querySelector("#daily-report"), options);
  chart.render();
}

if ($('#revenue-chart').length > 0) {
  var sCol = {
      chart: {
          height: 200,
          type: 'bar',
          stacked: true,
          toolbar: {
            show: false,
          }
      },
      plotOptions: {
          bar: {
              horizontal: false,
              columnWidth: '50%',
              endingShape: 'rounded'  
          },
      },
      dataLabels: {
          enabled: false
      },
      stroke: {
          show: true,
          width: 1,
      },
      series: [{
           name: 'High',
          color: '#0E82FD',
          data: [50,40,15,45,35,48,65]
      }],
      xaxis: {
          categories: ['M','T', 'W', 'T','F','S','S'],
      },
      tooltip: {
          y: {
              formatter: function (val) {
                  return "$ " + val + "k"
              }
          }
      }
  }
  
  var chart = new ApexCharts(
      document.querySelector("#revenue-chart"),
      sCol
  );
  
  chart.render();
}

// Employee Department

if ($('#emp-department').length > 0) {
  var sBar = {
    chart: {
      height: 220,
      type: 'bar',
      padding: {
        top: 0,
        left: 0,
        right: 0,
        bottom: 0
      },
      toolbar: {
        show: false,
      }
    },
    colors: ['#FF6F28'],
    grid: {
      borderColor: '#E5E7EB',
      strokeDashArray: 5,
      padding: {
        top: -20,
        left: 0,   
        right: 0,
        bottom: 0
      }
    },
    plotOptions: {
      bar: {
        borderRadius: 5,
        horizontal: true,
        barHeight: '35%',
        endingShape: 'rounded'
      }
    },
    dataLabels: {
      enabled: false
    },
    series: [{
      data: [80, 110, 80, 20, 60, 100],
      name: 'Employee'
    }],
    xaxis: {
      categories: ['UI/UX', 'Development', 'Management', 'HR', 'Testing', 'Marketing'],
      labels: {
        style: {
          colors: '#111827', 
          fontSize: '13px',
        }
      }
    }
  }

  var chart = new ApexCharts(
    document.querySelector("#emp-department"),
    sBar
  );

  chart.render();
}

// Company Chart

if ($('#company-chart').length > 0) {
  var sColStacked = {
    chart: {
      height: 240,
      type: 'bar',
      toolbar: {
        show: false,
      }
    },
    colors: ['#212529'],
    responsive: [{
      breakpoint: 480,
      options: {
        legend: {
          position: 'bottom',
          offsetX: -10,
          offsetY: 0
        }
      }
    }],
    plotOptions: {
      bar: {
        borderRadius: 10, 
        borderRadiusWhenStacked: 'all',
        horizontal: false,
        endingShape: 'rounded',
        colors: {
          backgroundBarColors: ['#f3f4f5'], // Background color for bars
          backgroundBarOpacity: 0.5,
          hover: {
            enabled: true,
            borderColor: '#F26522', // Color when hovering over the bar
          }
        }
      },
    },
    series: [{
      name: 'Company',
      data: [40, 60, 20, 80, 60, 60, 60]
    }],
    xaxis: {
      categories: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
      labels: {
        style: {
          colors: '#6B7280', 
          fontSize: '13px',
        }
      }
    },
    yaxis: {
      labels: {
        offsetX: -15,
        show: false 
      }
    },
    grid: {
      borderColor: '#E5E7EB',
      strokeDashArray: 5,
      padding: {
        left: -8,
      },
    },
    legend: {
      show: false
    },
    dataLabels: {
      enabled: false // Disable data labels
    },
    fill: {
      opacity: 1
    },
  }

  var chart = new ApexCharts(
    document.querySelector("#company-chart"),
    sColStacked
  );

  chart.render();
}

// Plan Chart

if ($('#plan-overview').length > 0) {
  var donutChart = {
    chart: {
      height: 240,
      type: 'donut',
      toolbar: {
        show: false,
      }
    },
    colors: ['#FFC107', '#1B84FF', '#F26522'],
    series: [20, 60, 20],
    labels: ['Enterprise', 'Premium', 'Basic'],
    plotOptions: {
      pie: {
        donut: {
          size: '50%',
          labels: {
            show: false
          },
          borderRadius: 30
        }
      }
    },
    stroke: {
      lineCap: 'round',
      show: true,
      width: 0,    // Space between donut sections
      colors: '#fff'
    },
    dataLabels: {
      enabled: false
    },
    legend: { show: false },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          height: 180,
        },
        legend: {
          position: 'bottom'
        }
      }
    }]
  }

  var donut = new ApexCharts(
    document.querySelector("#plan-overview"),
    donutChart
  );

  donut.render();
}

  // sales income

  if ($('#sales-income').length > 0) {
    var sColStacked = {
      chart: {
        height: 290,
        type: 'bar',
        stacked: true,
        toolbar: {
          show: false,
        }
      },
      colors: ['#FF6F28', '#F8F9FA'],
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
          }
        }
      }],
      plotOptions: {
        bar: {
          borderRadius: 5, 
          borderRadiusWhenStacked: 'all',
          horizontal: false,
          endingShape: 'rounded'
        },
      },
      series: [{
        name: 'Income',
        data: [40, 30, 45, 80, 85, 90, 80, 80, 80, 85, 20, 80]
      }, {
        name: 'Expenses',
        data: [60, 70, 55, 20, 15, 10, 20, 20, 20, 15, 80, 20]
      }],
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct','Nov', 'Dec'],
        labels: {
          style: {
            colors: '#6B7280', 
            fontSize: '13px',
          }
        }
      },
      yaxis: {
        labels: {
          offsetX: -15,
          style: {
            colors: '#6B7280', 
            fontSize: '13px',
          }
        }
      },
      grid: {
        borderColor: '#E5E7EB',
        strokeDashArray: 5,
        padding: {
          left: -8,
        },
      },
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false // Disable data labels
      },
      fill: {
        opacity: 1
      },
    }

    var chart = new ApexCharts(
      document.querySelector("#sales-income"),
      sColStacked
    );

    chart.render();
  }

  // Performance Chart
  if($('#performance_chart2').length > 0 ){
    var options = {
      series: [{
        name: "performance",
        data: [20, 20, 35, 35, 40, 60, 60]
    }],
      chart: {
      height: 273,
      type: 'area',
      zoom: {
        enabled: false
      }
    },
    colors: ['#03C95A'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'straight'
    },
    title: {
      text: '',
      align: 'left'
    },
    // grid: {
    //   row: {
    //     colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
    //     opacity: 0.5
    //   },
    // },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
    },
    yaxis: {
      min: 10,
      max: 60,
      tickAmount: 5,
          labels: {
            formatter: (val) => {
              return val / 1 + 'K'
            }
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'left'
        }
    };

    var chart = new ApexCharts(document.querySelector("#performance_chart2"), options);
    chart.render();
  }

  // Deals Stage

  if ($('#deals_stage').length > 0) {
    var sColStacked = {
      chart: {
        height: 310,
        type: 'bar',
        stacked: true,
        toolbar: {
          show: false,
        }
      },
      colors: ['#FF6F28', '#F8F9FA'],
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
          }
        }
      }],
      plotOptions: {
        bar: {
          borderRadius: 5, 
          horizontal: false,
          endingShape: 'rounded'
        },
      },
      series: [{
        name: 'Income',
        data: [80, 40, 100, 20]
      }, {
        name: 'Expenses',
        data: [100, 100, 100, 100]
      }],
      xaxis: {
        categories: ['Inpipeline', 'Follow Up', 'Schedule', 'Conversion'],
        labels: {
          style: {
            colors: '#6B7280', 
            fontSize: '13px',
          }
        }
      },
      yaxis: {
        labels: {
          offsetX: -15,
          style: {
            colors: '#6B7280', 
            fontSize: '13px',
          }
        }
      },
      grid: {
        borderColor: '#E5E7EB',
        strokeDashArray: 5
      },
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false // Disable data labels
      },
      fill: {
        opacity: 1
      },
    }

    var chart = new ApexCharts(
      document.querySelector("#deals_stage"),
      sColStacked
    );

    chart.render();
  }
  if ($('#top_deal_chart').length > 0) {
  var options = {
    series: [{
    name: 'Series 1',
    data: [80, 50, 30, 40, 100, 20],
    color: '#F37438',
    
  }, {
    name: 'Series 2',
    data: [20, 30, 40, 80, 20, 80],
    color: '#B359C3',
  }, {
    name: 'Series 3',
    data: [44, 76, 78, 13, 43, 10],
    color: '#1CCE6B',
  }],
    chart: {
    height: 200,
    type: 'radar',
    dropShadow: {
      enabled: true,
      blur: 1,
      left: 1,
      top: 1
    }
  },
  plotOptions: {
    radar: {
      spiderWeb: false
    }
  },
  stroke: {
    width: 2,
    curve: 'smooth' 
  },
  fill: {
    opacity: 1
  },
  markers: {
    size: 0
  },
  yaxis: {
    stepSize: 20
  },
  legend: {
    show: false,
  },
  xaxis: {
    categories: ['Mar', 'Feb', 'Jan', 'Aug', 'Jul', 'Jun' , 'May' , 'Apr']
  }
  };

  var chart = new ApexCharts(document.querySelector("#top_deal_chart"), options);
  chart.render();
}

  // Pipeline Chart
  if ($('#pipeline_chart').length > 0) {
  var options = {
    series: [
    {
      name: "",
      data: [1380, 1100, 990, 880, 740, 540],
    },
  ],
    chart: {
    type: 'bar',
    height: 280,
  },
  plotOptions: {
    bar: {
      borderRadius: 0,
      horizontal: true,
      distributed: true,
      barHeight: '80%',
      isFunnel: true,
    },
  },
  colors: [
    '#F26522',
    '#F37438',
    '#F5844E',
    '#F69364',
    '#F7A37A',
    '#F9B291'
  ],
  dataLabels: {
    enabled: true,
    formatter: function (val, opt) {
      return opt.w.globals.labels[opt.dataPointIndex] 
    },
    dropShadow: {
      enabled: true,
    },
  },
  title: {
    align: 'top',
  },
  xaxis: {
    categories: ['Marketing : 7,898', 'Sales : 4658', 'Email : 2898', 'Chat : 789', 'Operational : 655', 'Calls : 454'],
  },
  legend: {
    show: false,
  },
  };

  var chart = new ApexCharts(document.querySelector("#pipeline_chart"), options);
  chart.render();
}

  // Leads Stage

  if ($('#leads_stage').length > 0) {
    var sColStacked = {
      chart: {
        height: 355,
        type: 'bar',
        stacked: true,
        toolbar: {
          show: false,
        }
      },
      colors: ['#FF6F28', '#F8F9FA'],
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom',
            offsetX: -10,
            offsetY: 0
          }
        }
      }],
      plotOptions: {
        bar: {
          borderRadius: 5, 
          borderRadiusWhenStacked: 'all',
          horizontal: false,
          endingShape: 'rounded'
        },
      },
      series: [{
        name: 'Income',
        data: [80, 40, 60, 40]
      }, {
        name: 'Expenses',
        data: [100, 100, 100, 100]
      }],
      xaxis: {
        categories: ['Competitor', 'Budget', 'Unresponsie', 'Timing'],
        labels: {
          style: {
            colors: '#6B7280', 
            fontSize: '9px',
          }
        }
      },
      yaxis: {
        labels: {
          offsetX: -15,
          style: {
            colors: '#6B7280', 
            fontSize: '10px',
          }
        }
      },
      grid: {
        borderColor: '#E5E7EB',
        strokeDashArray: 5
      },
      legend: {
        show: false
      },
      dataLabels: {
        enabled: false // Disable data labels
      },
      fill: {
        opacity: 1
      },
    }

    var chart = new ApexCharts(
      document.querySelector("#leads_stage"),
      sColStacked
    );

    chart.render();
  }

  if ($('#donut-chart-2').length > 0) {
  var options = {
    series: [25, 30, 10, 35], // Percentages for each section
    chart: {
        type: 'donut',
        height: 185,
    },
    labels: ['Paid', 'Google', 'Referals', 'Campaigns'], // Labels for the data
    colors: ['#FFC107', '#0C4B5E', '#AB47BC', '#FD3995'], // Colors from the image
    plotOptions: {
        pie: { 
            donut: {
                size: '60%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Google',
                        formatter: function (w) {
                            return '40%';
                        }
                    }
                }
            }
        }
    },
    stroke: {
   
  lineCap: 'round',
  show: true,
  width: 0,    // Space between donut sections
  colors: '#fff'
  },
    legend: {
      show: false,
    },
    dataLabels: {
      enabled: false
    },
    label: {
      show: false,
    }
};

var chart = new ApexCharts(document.querySelector("#donut-chart-2"), options);
chart.render();
 }

 

 // Revenue income

 if ($('#revenue-income').length > 0) {
  var sColStacked = {
    chart: {
      height: 230,
      type: 'bar',
      stacked: true,
      toolbar: {
        show: false,
      }
    },
    colors: ['#FF6F28', '#F8F9FA'],
    responsive: [{
      breakpoint: 480,
      options: {
        legend: {
          position: 'bottom',
          offsetX: -10,
          offsetY: 0
        }
      }
    }],
    plotOptions: {
      bar: {
        borderRadius: 5, 
        borderRadiusWhenStacked: 'all',
        horizontal: false,
        endingShape: 'rounded'
      },
    },
    series: [{
      name: 'Income',
      data: [40, 30, 45, 80, 85, 90, 80, 80, 80, 85, 20, 80]
    }, {
      name: 'Expenses',
      data: [60, 70, 55, 20, 15, 10, 20, 20, 20, 15, 80, 20]
    }],
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct','Nov', 'Dec'],
      labels: {
        style: {
          colors: '#6B7280', 
          fontSize: '13px',
        }
      }
    },
    yaxis: {
      min: 0,    // Set the minimum value of the Y-axis to 0
      max: 100,
      labels: {
        offsetX: -15,
        style: {
          colors: '#6B7280', 
          fontSize: '13px',
        },
        formatter: function (value) {
          return value + "K"; // Divide by 1000 and append 'K'
        }
      }
    },
    grid: {
      borderColor: 'transparent',
      strokeDashArray: 5,
      padding: {
        left: -8,
      },
    },
    legend: {
      show: false
    },
    dataLabels: {
      enabled: false 
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return  val / 10 + " k"
        }
      }
    },
    fill: {
      opacity: 1
    },
  }

  var chart = new ApexCharts(
    document.querySelector("#revenue-income"),
    sColStacked
  );

  chart.render();
}


 if ($('#heat_chart').length > 0) {
  var options = {
    chart: {
      type: 'heatmap',
      height: 300,
  },
  colors: [
    "#9CA3AF",
    "#F37438",
    "#9CA3AF",
    "#F37438",
    "#9CA3AF",
    "#F37438",
  ],
    series: [
      {
        name: "0",
        data: [{
          x: 'Mon',
          y: 22
        },
        {
          x: 'Tue',
          y: 29
        }, 
        {
          x: 'Wed',
          y: 13
        }, 
        {
          x: 'Thu',
          y: 32
        },
        {
          x: 'Fri',
          y: 32
        },
        {
          x: 'Sat',
          y: 32
        },
        {
          x: 'Sun',
          y: 32
        },
      ]
      },
      {
        name: "20",
        data: [{
          x: 'Mon',
          y: 22,
          color: '#ff5722'
        },
        {
          x: 'Tue',
          y: 29
        }, 
        {
          x: 'Wed',
          y: 13
        }, 
        {
          x: 'Thu',
          y: 32
        },
        {
          x: 'Fri',
          y: 32
        },
        {
          x: 'Sat',
          y: 32
        },
        {
          x: 'Sun',
          y: 32
        },
      ]
      },
      {
        name: "40",
        data: [{
          x: 'Mon',
          y: 22
        },
        {
          x: 'Tue',
          y: 29
        }, 
        {
          x: 'Wed',
          y: 13
        }, 
        {
          x: 'Thu',
          y: 32
        },
        {
          x: 'Fri',
          y: 32
        },
        {
          x: 'Sat',
          y: 32
        },
        {
          x: 'Sun',
          y: 32
        },
      ]
      },
      {
        name: "60",
        data: [{
          x: 'Mon',
          y: 0
        },
        {
          x: 'Tue',
          y: 29
        }, 
        {
          x: 'Wed',
          y: 13
        }, 
        {
          x: 'Thu',
          y: 32
        },
        {
          x: 'Fri',
          y: 0
        },
        {
          x: 'Sat',
          y: 0
        },
        {
          x: 'Sun',
          y: 32
        },
      ]
      },
      {
        name: "80",
        data: [{
          x: 'Mon',
          y: 0
        },
        {
          x: 'Tue',
          y: 20
        }, 
        {
          x: 'Wed',
          y: 13
        }, 
        {
          x: 'Thu',
          y: 32
        },
        {
          x: 'Fri',
          y: 0
        },
        {
          x: 'Sat',
          y: 0
        },
        {
          x: 'Sun',
          y: 32
        },
      ]
      },
      {
        name: "120",
        data: [{
          x: 'Mon',
          y: 0
        },
        {
          x: 'Tue',
          y: 0
        }, 
        {
          x: 'Wed',
          y: 75
        }, 
        {
          x: 'Thu',
          y: 0
        },
        {
          x: 'Fri',
          y: 0
        },
        {
          x: 'Sat',
          y: 0
        },
        {
          x: 'Sun',
          y: 0
        },
      ]
      },
    ]
  };
  var chart = new ApexCharts(document.querySelector("#heat_chart"), options);
  chart.render();
}

if ($('#payslip-chart').length > 0) {
var options = {
  series: [{
  data: [22, 20, 30, 45, 55, 45, 20, 70, 25, 30, 10,30]
}],
  chart: {
  type: 'line',
  height: 200,
},
xaxis: {
  categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct','Nov', 'Dec'],
  labels: {
  }
},
stroke: {
  curve: 'stepline',
},
dataLabels: {
  enabled: false
},
markers: {
  hover: {
    sizeOffset: 4
  }
},
colors: ['#FF5733'],
};

var chart = new ApexCharts(document.querySelector("#payslip-chart"), options);
chart.render();
}

if ($('#user-chart').length > 0) {
var options = {
  series: [{
    name: 'Data',
    data: [34, 44, 54, 21, 12, 43, 33, 23, 66, 66, 58, 29] // Sample data for each month
  }],
  chart: {
    type: 'bar',
    height: 185
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '55%',
      endingShape: 'rounded'
    },
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    show: true,
    width: 2,
    colors: ['transparent']
  },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], // Months
  },
  fill: {
    opacity: 1
  },
  tooltip: {
    y: {
      formatter: function (val) {
        return val + " units";
      }
    }
  },
  colors: ['#00E396'] // Bar color (green in this case)
};

var chart = new ApexCharts(document.querySelector("#user-chart"), options);
chart.render();
}


// web app chart
if ($('#web_app_chart').length > 0) {
  var donutChart = {
    chart: {
      height: 284,
      type: 'donut',
      toolbar: {
        show: false,
      }
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '50%'
      },
    },
    dataLabels: {
      enabled: false
    },

    series: [6, 20, 5, 20, 15, 10, 10, 30],
    colors: ['#FF0000', '#8000FF', '#27EAEA', '#01B664', '#F9B801', '#24CDBA', '#AB47BC', '#FF7F00'],
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 252,
        },
        legend: {
          show: false
        }
      }
    }],
    legend: {
      show: false
    }
  }

  var donut = new ApexCharts(
    document.querySelector("#web_app_chart"),
    donutChart
  );

  donut.render();
}

if ($('#utilization').length > 0) {
  var sCol = {
    chart: {
      height: 350,
      type: 'bar',
      toolbar: {
        show: false,
      }
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '80%',
        // endingShape: 'rounded', // This rounds the top edges of the bars
        borderRadius: 5 // This is an example, not natively supported, but works with some hacks.
      },
    },
    colors: ['#3550DC', '#FE9738', '#27EAEA'],
    dataLabels: {
      enabled: true
    },
    stroke: {
      show: true,
      width: 2,
      colors: ['transparent']
    },
    series: [{
      name: 'Inprogress',
      data: [60, 80, 100, 70, 30, 50, 70, 90, 50, 70]
    }, {
      name: 'Active',
      data: [30, 20, 2, 10, 60, 20, 30, 2, 20, 10]
    }, 
    {
      name: 'Completed',
      data: [10, 2, 2, 20, 10, 30, 2, 10, 30, 20]
    }],
    xaxis: {
      categories: ['1 hr', '2 hr', '3 hr', '4 hr', '5 hr', '6 hr', '7 hr', '8 hr', '9 hr', '10 hr'],
    },
    fill: {
      opacity: 1
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return "" + val + "%"
        }
      }
    }
  }

  var chart = new ApexCharts(
    document.querySelector("#utilization"),
    sCol
  );

  chart.render();
}


// app chart
if ($('#car-chart').length > 0) {
  var sCol = {
    chart: {
      width: '100',
      height: 90,
      type: 'bar',
      toolbar: {
        show: false,
      },
      padding: 0
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '100%', // Removes space between bars
          barHeight: '100%',
        endingShape: 'rounded'
      },
    },
    colors: ['#F0ECFF', '#4361ee'],
    states: {
      hover: {
          filter: {
              type: 'darken', // Options: 'none', 'lighten', 'darken'
              value: 0.3 // Adjust hover intensity
          }
      }
  },
    dataLabels: {
      enabled: false
    },
    stroke: {
      show: true,
      width: 2,
      colors: ['transparent']
    },
    series: [{
      name: 'Car',
      data: [9, 4, 7, 7, 4, 9, 8]
    }],
    fill: {
      opacity: 1

    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'July'],
      labels: { show: false },
      axisTicks: { show: false },
      axisBorder: { show: false } 
  },
  grid: {
    show: false, // Set false to hide all grid lines
    padding: { left: 0, right: 0, top: -15, bottom: 0 } 
},
    yaxis: {
      labels: { show: false }  // Hides Y-axis values
  },
    tooltip: {
      y: {
        formatter: function (val) {
          return  val 
        }
      }
    }
  }

  var chart = new ApexCharts(
    document.querySelector("#car-chart"),
    sCol
  );

  chart.render();
}


if ($('#locationChart').length > 0) {
  var sCol1 = {
    series: [
    {
      group: 'budget',
      data: [20000]
    },
    {
      group: 'budget',
      data: [40000]
    },
  ],
    chart: {
    type: 'bar',
    height: 250,
    stacked: true,
  },
  stroke: {
    width: 1,
    colors: ['#fff']
  },
  dataLabels: {
    formatter: (val) => {
      return val / 1000 + 'K'
    }
  },
  plotOptions: {
    bar: {
      horizontal: true
    }
  },
  xaxis: {
    labels: {
      formatter: (val) => {
        return val / 1 + ''
      }
    }
  },
  fill: {
    opacity: 1,
  },
  colors: ['#3550DC', '#FE9738', ],
  };

  var chart = new ApexCharts(document.querySelector("#chart"), options);
  chart.render();

  var chart = new ApexCharts(
    document.querySelector("#locationChart"),
    sCol1
  );

chart.render();
}

if ($('#summary_chart').length > 0) {
  var options = {
    chart: {
      type: 'heatmap',
      height: 330,
  },
  plotOptions: {
    heatmap: {
      radius: 5,
      enableShades: false,
      colorScale: {
        ranges: [{
            from: 1,
            to: 70,
            color: '#FFF5ED'
          },
          {
            from: 0,
            to: 0,
            color: '#FFCDA4'
          },
          {
            from: 80,
            to: 100,
            color: '#FE9738'
          },
        ],
      },
  
    }
  },
  legend: {
    show: false
  },
  dataLabels: {
    enabled: false
  },
 stroke: {
    width: 5, 
    colors: ['#fff']
  },
  grid: {
    borderColor: '#333',
  strokeDashArray: 7,
},
xaxis: {
  lines: {
    show: false,
  }
},
  grid: {
    show: false,
   xaxis: {
      lines: {
        show: false,
      }
    },
    padding: {
      top: -30,
      bottom: 0,
      left: 0,
      right: -20, // Minimize padding around the heatmap
    },
  },
  xaxis: {
    axisBorder: { show: false }, 
    axisTicks: { show: false } // ❌ Removes axis ticks
},
  yaxis: {
    labels: {
      offsetX: -15, // Adjust horizontal alignment
    },
  },
    series: [
      {
        name: "Sat",
        data: [{
          x: '09:00',
          y: 90
        },
        {
          x: '10:00',
          y: 100
        }, 
        {
          x: '11:00',
          y: 0
        }, 
        {
          x: '12:00',
          y: 50
        },
        {
          x: '01:00',
          y: 90
        },
        {
          x: '02:00',
          y: 90
        },
        {
          x: '04:00',
          y: 60
        },
        {
          x: '05:00',
          y: 100
        },
        {
          x: '06:00',
          y: 90
        },
        {
          x: '07:00',
          y: 80
        },
        {
          x: '08:00',
          y: 70
        },
      ]
      },
      {
        name: "Fri",
        data: [{
          x: '09:00',
          y: 50
        },
        {
          x: '10:00',
          y: 60
        }, 
        {
          x: '11:00',
          y: 60
        }, 
        {
          x: '12:00',
          y: 50
        },
        {
          x: '01:00',
          y: 0
        },
        {
          x: '02:00',
          y: 60
        },
        {
          x: '04:00',
          y: 60
        },
        {
          x: '05:00',
          y: 0
        },
        {
          x: '06:00',
          y: 0
        },
        {
          x: '07:00',
          y: 0
        },
        {
          x: '08:00',
          y: 70
        },
      ]
      },
      {
        name: "Thu",
        data: [{
          x: '09:00',
          y: 50
        },
        {
          x: '10:00',
          y: 0
        }, 
        {
          x: '11:00',
          y: 60
        }, 
        {
          x: '12:00',
          y: 90
        },
        {
          x: '01:00',
          y: 60
        },
        {
          x: '02:00',
          y: 0
        },
        {
          x: '04:00',
          y: 0
        },
        {
          x: '05:00',
          y: 100
        },
        {
          x: '06:00',
          y: 60
        },
        {
          x: '07:00',
          y: 80
        },
        {
          x: '08:00',
          y: 70
        },
      ]
      },
      {
        name: "Wed",
        data: [{
          x: '09:00',
          y: 0
        },
        {
          x: '10:00',
          y: 0
        }, 
        {
          x: '11:00',
          y: 60
        }, 
        {
          x: '12:00',
          y: 0
        },
        {
          x: '01:00',
          y: 90
        },
        {
          x: '02:00',
          y: 0
        },
        {
          x: '04:00',
          y: 0
        },
        {
          x: '05:00',
          y: 0
        },
        {
          x: '06:00',
          y: 0
        },
        {
          x: '07:00',
          y: 90
        },
        {
          x: '08:00',
          y: 0
        },
      ]
      },
      {
        name: "Tue",
        data: [{
          x: '09:00',
          y: 0
        },
        {
          x: '10:00',
          y: 50
        }, 
        {
          x: '11:00',
          y: 0
        }, 
        {
          x: '12:00',
          y: 0
        },
        {
          x: '01:00',
          y: 60
        },
        {
          x: '02:00',
          y: 90
        },
        {
          x: '04:00',
          y: 0
        },
        {
          x: '05:00',
          y: 90
        },
        {
          x: '06:00',
          y: 0
        },
        {
          x: '07:00',
          y: 0
        },
        {
          x: '08:00',
          y: 0
        },
      ]
      },
      {
        name: "Mon",
        data: [{
          x: '09:00',
          y: 90
        },
        {
          x: '10:00',
          y: 0
        }, 
        {
          x: '11:00',
          y: 90
        }, 
        {
          x: '12:00',
          y: 80
        },
        {
          x: '01:00',
          y: 80
        },
        {
          x: '02:00',
          y: 90
        },
        {
          x: '04:00',
          y: 80
        },
        {
          x: '05:00',
          y: 90
        },
        {
          x: '06:00',
          y: 90
        },
        {
          x: '07:00',
          y: 90
        },
        {
          x: '08:00',
          y: 0
        },
      ]
      },
    ]
  };
  var chart = new ApexCharts(document.querySelector("#summary_chart"), options);
  chart.render();
}

if($('#hours_chart').length > 0) {
  var options = {
    series: [{
    name: 'Worked Hours',
    data: [8, 6, 5, 6, 5, 7],
    }, ],
    grid: {
      show: false,
      xaxis: {
        lines: {
          show: false,
        }
      },
      padding: {
        top: 5, // Adds space on the left
        right: 5, // Adds space on the right
      },
    },
    colors: ['#E8EEFE'],
    chart: {
      type: 'bar',
      height: 250,
      stacked: true,        
      zoom: {
        enabled: true
      }
    },
    responsive: [{
    breakpoint: 280,
    options: {
      legend: {
      position: 'bottom',
      offsetY: 0
      }
    }
    }],
    plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: '90%',
    },
    },
      dataLabels: {
      enabled: false
    },
      yaxis: {
        labels: {
          offsetX: -15,
          formatter: (val) => {
            return val / 1 + 'h'
          },
        },
          min: 0,
          max: 12,
          tickAmount: 6,
        },
    xaxis: {
    categories: [' Mon', 'Tue', 'Wed', 'Thu',
      'Fri', 'Sat'
    ],
    },
    grid: {
      padding: {
        right: -10, 
      },
    },
    legend: {show: false},
    fill: {
    opacity: 1
    },
    states: {
      hover: {
        filter: {
          type: 'none', // Disable default filter effect
        },
        color: '#3550DC' // Change to your primary color
      }
    },
    };
    

    var chart = new ApexCharts(document.querySelector("#hours_chart"), options);
    chart.render();
  }

// Sales Statistics
if ($('#total_sales').length > 0) {
    var options = {
        series: [25, 50, 25], // Adjusted to look like the image proportions
        chart: {
            type: 'donut',
            height: 280,
            sparkline: {
                enabled: true // Removes extra padding for a cleaner look
            }
        },
        labels: ['Dell XPS 13', 'Nike T-shirt', 'Apple iPhone 15'],
        colors: ['#8B5CF6', '#EAB308', '#22C55E'], // Matches the image vibrant tones
        plotOptions: {
            pie: {
                startAngle: -90,
                endAngle: 90,
                offsetY: 30,
                donut: {
                    size: '75%', // Thinner ring to match screenshot
                }
            }
        },
        // Adds the gradient effect seen in the screenshot
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: "vertical",
                opacityFrom: 1,
                opacityTo: 0.6, // Gives it that soft fade at the bottom
            }
        },
        stroke: {
            show: true,
            width: 3,
            colors: ['#fff'] // The white gaps between segments
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false
        },
        tooltip: {
            enabled: true
        }
    };

    var chart = new ApexCharts(document.querySelector("#total_sales"), options);
    chart.render();
}

// Simple Line Area
if ($('#invoice_income').length > 0) {
  var sLineArea = {
    chart: {
      height: 76,
      type: 'area',
      background: 'transparent', // Changed to transparent to blend with card
      sparkline: {
        enabled: true // This is the best way to remove all labels/grids instantly
      },
      toolbar: {
        show: false,
      }
    },
    colors: ['#27AE60'],
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.4, 
        opacityTo: 0.1,   
        stops: [0, 90, 100]
      }
    },
    // Moved dataLabels out of fill block to fix syntax
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 2
    },
    series: [{
      name: 'Income',
      data: [30, 35, 45, 40, 55, 45, 56, 53, 68, 63, 70, 80]
    }],
    grid: {
      show: false,
      padding: { left: 0, right: 0, top: 0, bottom: 0 } 
    },
    yaxis: {
      show: false
    },
    xaxis: {
      labels: { show: false },
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    tooltip: {
      enabled: false // Removes the popup label when hovering
    }
  }

  var chart = new ApexCharts(
    document.querySelector("#invoice_income"),
    sLineArea
  );

  chart.render();
}

// Revenue
if ($('#revenue_chart').length > 0) {
  var sColStacked = {
    chart: {
      height: 318,
      type: 'bar',
      stacked: true,
      toolbar: {
        show: false,
      }
    },
    responsive: [{
      breakpoint: 480,
      options: {
      }
    }],
    plotOptions: {
      bar: {
        horizontal: false,
        borderRadius: 5, 
        borderRadiusWhenStacked: 'all',
        endingShape: 'rounded',
        columnWidth: '40%'
      },
    },
    legend: {
      show: false,
    },
    dataLabels: {
    enabled: false
  },
    label: {
      show: false,
    },
    colors: ['#7539FF', '#F8F5FF'],
    series: [ {
      name: 'Outstanding',
      data: [0, 10, 30, 50, 25, 38, 40]
    },{
      name: 'Received ',
      data: [30, 30, 80, 70, 80, 80, 80]
    },],
    grid: {
      borderColor: '#E2E4E6',
      strokeDashArray: 5,
      padding: {
        right: -10, left: -10, 
      },
    },
    xaxis: {
      categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    },
    yaxis: {
      min: 0,
      max: 100,
      labels: { show: false }
    },
    fill: {
      opacity: 1
    },
  }

  var chart = new ApexCharts(
    document.querySelector("#revenue_chart"),
    sColStacked
  );

  chart.render();
}

// Revenue
if ($('#sales_analytics').length > 0) {
  var sColStacked = {
    chart: {
      height: 300,
      type: 'bar',
      toolbar: {
        show: false,
      }
    },
    plotOptions: {
      bar: {
        horizontal: false,
      },
    },
    legend: {
      show: false,
    },
    dataLabels: {
    enabled: false
  },
    label: {
      show: false,
    },
    colors: ['#2F80ED', '#E2B93B'],
    series: [ {
      name: 'Received ',
      data: [80, 100, 50, 50, 80, 60, 100, 60, 40, 55, 30, 70]
    },{
      name: 'Pending ',
      data: [40, 60, 60, 60, 20, 80, 40, 20, 50, 70, 40, 60]
    },],
    grid: {
      borderColor: '#E2E4E6',
      strokeDashArray: 5,
      padding: {
        right: -10, left: 0, 
      },
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    },
    yaxis: {
      min: 0,
      max: 100,
      labels: {offsetX: -15, 
        formatter: (val) => {
          return '$' + val / 1 
        }
      }
    },
    fill: {
      opacity: 1
    },
  }

  var chart = new ApexCharts(
    document.querySelector("#sales_analytics"),
    sColStacked
  );

  chart.render();
}

// Sales Statistics

if ($('#invoice_analytics').length > 0) {
  var options = {
    series: [40, 35, 25], // Percentages for each section
    chart: {
        type: 'donut',
        height: 240,
    },
    labels: [ 'Invoiced', 'Received', 'Pending'], // Labels for the data
    colors: ['#2F80ED', '#27AE60', '#E2B93B'], // Colors from the image
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: false,
                    total: {
                        show: true,
                        label: 'Leads',
                        formatter: function (w) {
                            return '589';
                        }
                    }
                }
            }
        }
    },
    dataLabels: {
      enabled: true
    },
    legend: {
      show: true,
      position: "bottom", 
    },
    label: {
      show: false,
    }
};

var chart = new ApexCharts(document.querySelector("#invoice_analytics"), options);
chart.render();
 }

// Products Chart
if ($('#products-chart').length > 0) {
	const options = {
    series: [{// Product names as series names
      data: [115, 88, 62] // Approximate values from your image
    }],
    chart: {
      type: 'bar',
      height: 190,
      toolbar: { show: false }
    },
    plotOptions: {
      bar: {
        borderRadius: 6, // Creates the rounded top effect
        borderRadiusApplication: 'around',
        columnWidth: '70%',
        dataLabels: {
          position: 'bottom', // Positions labels at the base of the bar
          orientation: 'vertical' 
        },
      }
    },
    dataLabels: {
      enabled: true,
      textAnchor: 'center',
      offsetY: 10, 
      formatter: function (val, opt) {
        // Maps the category name to the bar instead of the numeric value
        const labels = ['Apple iPhone 15', 'Nike T-shirt', 'Dyson V15'];
        return labels[opt.dataPointIndex];
      },
      style: {
        colors: ['#fff'],
        fontSize: '14px',
        fontWeight: 500,
      }
    },
    fill: {
			type: 'gradient',
			gradient: {
				type: 'vertical',
				shadeIntensity: 0,
				inverseColors: false, // Ensure top stays top and bottom stays bottom
				opacityFrom: 1,
				opacityTo: 1,
				stops: [0, 100],
				colorStops: [
					{
						offset: 0,
						color: "#C9B2FF", // Top Color (100% in your CSS)
						opacity: 1
					},
					{
						offset: 100,
						color: "#7539FF", // Bottom Color (0% in your CSS)
						opacity: 1
					}
				]
			}
		},
    colors: ['#D1C4FF'], // Starting color for the gradient
    xaxis: {
      categories: ['Apple iPhone 15', 'Nike T-shirt', 'Dyson V15'],
      labels: { show: false },
      axisTicks: { show: false },
      axisBorder: { show: false }
    },
    yaxis: {
      min: 0,
      max: 125,
      tickAmount: 5,
      labels: {
        offsetX: -15,
      },
    },
    tooltip: {
      enabled: true,
      marker: {
        show: false, // Removes the circle icon
      },
      y: {
        title: {
          formatter: () => '', // Removes the series name ("series-1" or "Sales")
        },
      },
    },
    grid: {
      borderColor: '#f1f1f1',
      strokeDashArray: 4, // Creates the dashed background lines seen in your image
      padding: { left: 0, right: 0, top: -15, bottom: 0 }
    }
  };
	const chart4 = new ApexCharts(document.querySelector("#products-chart"), options);
	chart4.render();
}

if ($('#sales-chart').length > 0) {

  var options = {
    chart: {
      type: 'bar',
      height: 90,
      toolbar: { show: false },
      sparkline: { enabled: true },
    },

    plotOptions: {
      bar: {
        horizontal: true,
        barHeight: '55%',
        borderRadius: 8,
        distributed: true,
        colors: {
					backgroundBarColors: ['#DFDCFF'],
          backgroundBarRadius: 8,
        }
      }
    },

    series: [{
      name: "Sales",
      data: [80]
    }],

    xaxis: {
      categories: [
        'Sales'
      ],
      fontSize: '14px',
      min: 0,
      max: 100,
      tickAmount: 4,
      labels: {
        style: {
          fontSize: '12px'
        },
        formatter: function (val) {
          return "$" + val.toFixed(1) + "M";
        }
      }
    },
    yaxis: {
      labels: {
          style: {
          fontSize: '12px',          
        },
      }
    },

    colors: [
      '#C594FA',  // Purple start
    ],
     tooltip: {
		marker: false,
	},
    fill: {
      type: 'gradient',
      gradient: {
        type: 'horizontal',
        shadeIntensity: 0,
        opacityFrom: 1,
        opacityTo: 1,
        gradientToColors: [
          '#7F24E3', 
        ],
        stops: [0, 100]
      }
    },

    dataLabels: { enabled: false },
    grid: { show: false },
    stroke: { show: false },
    legend: { show: false }
  };

  var chart = new ApexCharts(
    document.querySelector("#sales-chart"),
    options
  );

  chart.render();
}

// Revenue & Expenses Chart
if ($('#revenue-expense-chart').length > 0) {

  var options = {
    chart: {
      type: 'area',
      height: 300,
      toolbar: { show: false }
    },

    series: [
      {
        name: "Revenue",
        data: [50, 55, 53, 50, 60, 55, 58, 52, 55, 52, 62, 55]
      },
      {
        name: "Expense",
        data: [10, 15, 13, 10, 14, 11, 13, 10, 18, 12, 20, 18]
      }
    ],

    stroke: {
      curve: 'smooth',
      width: 2
    },

    colors: ['#3B44F6', '#22C55E', '#FF3B30'],

    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.35,
        opacityTo: 0.05,
        stops: [0, 100]
      }
    },

    dataLabels: { enabled: false },

    xaxis: {
      categories: [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
      labels: {
        style: { fontSize: '12px' }
      }
    },

    yaxis: {
      min: 0,
      max: 80,
      labels: {
        offsetX: -15,
        formatter: function (val) {
          return "$" + val + "K";
        },
        style: { fontSize: '12px' }
      }
    },

    grid: {
      borderColor: '#e5e7eb',
      strokeDashArray: 4,
      padding: { left: 0, right: 0, top: 0, bottom: 0 } 
    },

    tooltip: {
      marker: false,
      shared: true,
      intersect: false,
      y: {
        formatter: function (val) {
          return "$" + val + "K";
        }
      }
    },

    legend: {
      show: false   // ✅ removed legend
    }
  };

  var chart = new ApexCharts(
    document.querySelector("#revenue-expense-chart"),
    options
  );

  chart.render();
}

//Payment Chart
if ($('#payment-chart').length > 0) {

    var options = {
        chart: {
            type: 'donut',
            height: 250,
        },

        series: [139500, 90568, 29510, 10142],

        labels: ['Credit Card', 'Bank Transfer', 'Digital Wallet', 'Cash'],

        colors: ['#CFCBFF', '#B2A7FF', '#9781FF', '#7539FF'],
        plotOptions: {
            pie: {
                expandOnClick: false,
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: false
                        }
                    }
                }
            }
        },

        stroke: {
            width: 2,
            colors: ['#fff'] // white spacing between segments
        },

        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Math.round(val) + "%";
            },
            style: {
                fontSize: '12px',
                fontWeight: 600
            },
            dropShadow: {
                enabled: false
            }
        },

        legend: {
            show: false   // ✅ No legend
        },

        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toLocaleString();
                }
            }
        }
    };

    var chart = new ApexCharts(
        document.querySelector("#payment-chart"),
        options
    );

    chart.render();
}

//Payment Chart
if ($('#breakdown-chart').length > 0) {

    const options = {
      series: [
        { name: 'Operating', data: [5, 5, 5, 5, 5, 10, 10] },
        { name: 'Payroll', data: [4, 4, 4, 10, 5, 15, 8] },
        { name: 'Marketing', data: [15, 30, 25, 15, 30, 10, 15] }
      ],
      chart: {
        type: 'bar',
        height: 170,
        stacked: true, // Crucial for this look
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          columnWidth: '60%',
          borderRadius: 5, // Gives the rounded look to the overall bar
          borderRadiusApplication: 'around',
          borderRadiusWhenStacked: 'all', 
        }
      },
      colors: ['#A495FF', '#8CB9FE', '#7DCEA0'], // Purple, Blue, Green
      stroke: {
        show: true,
        width: 3, // This creates the white "gap" between segments
        colors: ['#fff']
      },
      dataLabels: { enabled: false },
      xaxis: { 
        categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        min: 0,
        max: 40,
        labels: {
          offsetX: -15, 
          formatter: (val) => val + "K",
          style: { colors: '#9e9e9e' }
        }
      },
      grid: {
        borderColor: '#f1f1f1',
        strokeDashArray: 4,
        padding: {
          right: 0, left: -15, bottom: -12
        },
        xaxis: { lines: { show: false } }
      },
      legend: { show: false },
      tooltip: {
        marker: { show: false },
        y: {
          title: { formatter: () => '' } // Removes the name as requested earlier
        }
      }
    };


    var chart = new ApexCharts(
        document.querySelector("#breakdown-chart"),
        options
    );

   chart.render().then(() => {
    // Select all rects of the middle series (series index 1)
    const middleRects = document.querySelectorAll('.apexcharts-series[data\\:index="1"] rect');
    middleRects.forEach(rect => rect.setAttribute('rx', 5)); // Set horizontal radius
  });
}

//Payment Chart
if ($('#invoice-chart').length > 0) {
	var options = {
		series: [{
      name: 'Invoice',
			data: [80]
		}],
		chart: {
			type: 'bar',
			height: 70,
			toolbar: {
				show: false
			}
		},

		plotOptions: {
			bar: {
				horizontal: true,
       borderRadius: 10,
       borderRadiusApplication: 'around',
			}
		},
		dataLabels: {
			enabled: false
		},
		fill: {
			type: 'gradient',
			gradient: {
				type: 'vertical',
				shadeIntensity: 0,
				inverseColors: false, // Ensure top stays top and bottom stays bottom
				opacityFrom: 1,
				opacityTo: 1,
				stops: [0, 100],
				colorStops: [
					{
						offset: 0,
						color: "#7539FF", // Top Color (100% in your CSS)
						opacity: 1
					},
					{
						offset: 100,
						color: "#462299", // Bottom Color (0% in your CSS)
						opacity: 1
					}
				]
			}
		},
		grid: {
			show: false,
      padding: {
        left: -15,
        right: 10,
        top: -25,
        bottom: -14,
      },
		},
		tooltip: {
			marker: false,
		},
		xaxis: {
       min: 0,
        max: 80,
			categories: ['Invoice'], 
      labels: {
        offsetX: -8, 
        formatter: function (val) {
                return val + "k"; // Converts 20000 to 20k
            }
      },
       axisBorder: { show: false },
      axisTicks: { show: false }
		},
		yaxis: {
			show: false,
		}
	};

	var chart = new ApexCharts(document.querySelector("#invoice-chart"), options);
	chart.render();
}

//Payment Chart
if ($('#inv-payment-chart').length > 0) {
    var options = {
    series: [{
          name: '1st Quarter',
          data: [8, 8]
        }, {
          name: '2nd',
          data: [10, 7]
        },{
          name: '3rd',
          data: [5, 5]
        },{
          name: '4th',
          data: [10, 9]
        },],
    chart: {
      type: 'bar',
      height: 70,
      toolbar: { show: false }
    },
    stroke: {
      show: true,
      width: 8, // Increase this number to create a wider "gap" (transparent border)
      colors: ['transparent']
    },
    plotOptions: {
      bar: {
        columnWidth: '90%', // Makes them look like thin ticks
        borderRadius: 2,
      }
    },
    // Map colors: Light gray for normal, Dark for the "active" tick
    colors: ['#D1D5DB', '#D1D5DB'],
    dataLabels: { enabled: false },
    legend: { show: false },   
		grid: {
			show: false,
      padding: {
        left: 0,
        right: -15,
        top: -25,
        bottom: -12,
      },
		},
    xaxis: {
      categories: ['1M', '1.2M'],
      axisBorder: { show: false },
      axisTicks: { show: false }
    },
    yaxis: { 
      show: false,
    },
    tooltip: { 
      enabled: true,
      marker: false,
      y: {
        formatter: function(val) {
          return val + "k"; // Shows the actual data value
        },
        title: {
        // This removes the series name/number "1" from the left of the value
        formatter: () => '', 
      },

      },
    }
  };


	var chart = new ApexCharts(document.querySelector("#inv-payment-chart"), options);
	chart.render();
}

// Sales Analytics Chart
if ($('#sales-analytics').length > 0) {

  var options = {
    chart: {
      type: 'area',
      height: 300,
      toolbar: { show: false }
    },

    series: [
      {
        name: "Pending",
        data: [21, 30, 35, 18, 20, 23, 20, 18, 23, 28, 35, 30]
      },
      {
        name: "Received",
        data: [10, 15, 18, 22, 30, 20, 23, 25, 15, 12, 18, 20]
      }
    ],

    stroke: {
      curve: 'smooth',
      width: 2
    },

    colors: ['#3B44F6', '#22C55E', '#FF3B30'],

    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.35,
        opacityTo: 0.05,
        stops: [0, 100]
      }
    },

    dataLabels: { enabled: false },

    xaxis: {
      categories: [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
      labels: {
        style: { fontSize: '12px' }
      }
    },

    yaxis: {
      min: 0,
      max: 60,
      labels: {
        offsetX: -15,
        formatter: function (val) {
          return "$" + val + "K";
        },
        style: { fontSize: '12px' }
      }
    },

    grid: {
      borderColor: '#e5e7eb',
      strokeDashArray: 4,
      padding: { left: 0, right: 0, top: 0, bottom: 0 } 
    },

    tooltip: {
      marker: false,
      shared: true,
      intersect: false,
      y: {
        formatter: function (val) {
          return "$" + val + "K";
        }
      }
    },

    legend: {
      show: false   // ✅ removed legend
    }
  };

  var chart = new ApexCharts(
    document.querySelector("#sales-analytics"),
    options
  );

  chart.render();
}

// transaction-statistics Chart
if ($('#transaction-statistics').length > 0) {

  var options = {
    chart: {
      type: 'area',
      height: 390,
      toolbar: { show: false },
      zoom: { enabled: false }
    },

    series: [
      {
        name: "Purchase",
        data: [42, 47, 56, 50, 54, 48, 50, 46, 39, 41, 45, 44]
      },
      {
        name: "Paid",
        data: [30, 35, 46, 39, 44, 38, 39, 35, 27, 29, 34, 33]
      }
    ],

    stroke: {
      curve: 'stepline',   // step line like image
      width: 2
    },

    colors: ['#7539FF', '#27AE60'],

    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.35,
        opacityTo: 0.05,
        stops: [0, 100]
      }
    },

    dataLabels: {
      enabled: false
    },

    xaxis: {
      categories: [
        'Jan','Feb','Mar','Apr','May','Jun',
        'Jul','Aug','Sep','Oct','Nov','Dec'
      ],
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
        style: { fontSize: '12px' }
      }
    },

    yaxis: {
      min: 0,
      max: 70,
      tickAmount: 7,      
      labels: {
        offsetX: -10,
        formatter: function(val) {
          return val + "K";
        },
        style: { fontSize: '12px' }
      }
    },

    grid: {
      borderColor: '#e5e7eb',
      strokeDashArray: 0,
      padding: {
        top: -10,
        right: 0,
        bottom: -10,
        left: 0
      }
    },

    tooltip: {
      shared: true,
      intersect: false,
      marker: {
        show: false
      },
      y: {
        formatter: function(val) {
          return "$" + val + ",000";
        }
      }
    },

    legend: {
      show: false
    }
  };
  

  var chart = new ApexCharts(
    document.querySelector("#transaction-statistics"),
    options
  );

  chart.render();
}


// Profit Revenue
if ($('#payment-category').length > 0) {
	var options = {
    series: [{
        name: 'Income',
        data: [90, 70, 90, 80, 90],
    }, {
        name: 'Expenses',
        data: [60, 60, 50, 90, 86],
    }],
    // Matches the blue and pink/red theme from your image
    colors: ['#5654D41A', '#FB3B5233'], 
    chart: {
        height: 300,
        type: 'radar',
        toolbar: { show: false },
          parentHeightOffset: 0, 
        dropShadow: {
            enabled: true,
            blur: 1,
            left: 1,
            top: 1
        }
    },
    legend: {
        show: false
    },
    fill: {
        opacity: 0.7, // Semi-transparent as seen in the image
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['#5654D4', '#FB3B52'],
    },
    markers: {
        size: 4, // Shows the points at each intersection
        colors: ['#fff'],
        strokeColors: ['#5654D4', '#FB3B52'],
        strokeWidth: 2,
    },
    plotOptions: {
        radar: {
          size: 110, 
            polygons: {
                strokeColors: '#e8e8e8',
                connectorColors: '#e8e8e8',
                fill: {
                    colors: ['#f8f8f8', '#fff']
                }
            }
        }
    },
    xaxis: {
        categories: ['Stripe', 'UPI', 'Bank Transfer', 'Credit card', 'Paypal'],
        labels: {
            show: true,
            style: {
                colors: ["#333"],
                fontSize: '12px',
            }
        }
    },
    yaxis: {
        show: false,
        tickAmount: 5,
    },
    padding: {
            top: -50,
            bottom: -20
    },
    tooltip: {
       marker: false,
       y: {
        formatter: function (val) {
          return "$" + val + "K";
        }
      }
    }
  };

	const chart = new ApexCharts(document.querySelector("#payment-category"), options);
	chart.render();
}

// Invoice Analytics
if ($('#invoice-analytics').length > 0) {
    var options = {
    series: [{
      data: [70000, 42000, 28000]
    }],
    chart: {
      type: 'bar',
      height: 250,
      // Increase left margin if the text touches the very edge of the screen
      margin: { left: 10 }, 
      toolbar: { show: false }
    },
    legend: {
          show: false
      },
    plotOptions: {
      bar: {
        horizontal: true,
        barHeight: '50%',
        distributed: true,
        borderRadius: 6,
        colors: {
          backgroundBarColors: ['#FFF8E1', '#FFF0ED', '#F5F2FF'],
          backgroundBarRadius: 6,
          backgroundBarOpacity: 1,
        },
      }
    },
    fill: {
    	opacity: 0.7,
			type: 'gradient',
			gradient: {
				type: 'radial', // Matches your CSS intent
				shadeIntensity: 1,
				opacityFrom: 1,
				opacityTo: 1,
				stops: [3, 100], // Matches your '3%' and '100%' stops
				colorStops: [
					// Color Stops for Series 1 (Green)
					[
						{ offset: 3, color: "#E2B93B", opacity: 0.8 },
						{ offset: 100, color: "#FFE184", opacity: 0.8 }
					],
					// Color Stops for Series 2 (Purple)
					[
						{ offset: 3, color: "#E04F16", opacity: 0.8 },
						{ offset: 100, color: "#FFDED0", opacity: 0.8 }
					],
					// Color Stops for Series 2 (Purple)
					[
						{ offset: 3, color: "#7539FF4D", opacity: 0.8 },
						{ offset: 100, color: "#7539FF", opacity: 0.8 }
					]
				]
			}
		},
    yaxis: {
      labels: {
        show: true,
        align: 'left',
        // 1. Set widths to 0 to remove the reserved Y-axis space
        // 2. Adjust offsetX to pull the text from the "hidden" area into the chart
        offsetX: 0, 
        offsetY: -22,
        style: {
          fontSize: '15px',
          colors: '#444'
        }
      }
    },
    xaxis: {
      categories: ['Invoiced', 'Received', 'Pending'],
      max: 100000,
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
          offsetX: -15,
        formatter: (val) => val / 1000 + "K",
        // Ensures the 0K label starts exactly at the left edge
        offsetY: 0,
      }
    },
    grid: {
      padding: {
        // 3. Remove default padding so bars touch the left axis line
        left: 0, 
        right: 10
      },
      xaxis: {
              lines: { show: true } 
          },
          yaxis: {
              lines: { show: false } // Hides horizontal lines
          },
          strokeDashArray: 4,
    },
    dataLabels: { enabled: false },
    tooltip: {
       marker: false,
       y: {
        formatter: function (val) {
          return "$" + val + "K";
        },title: {
          formatter: () => '', // Removes the series name ("series-1" or "Sales")
        },
      }
    }
  };

	const chart = new ApexCharts(document.querySelector("#invoice-analytics"), options);
	chart.render();
}

if ($('#sales-performance').length > 0) {

	var options = {
    series: [{
      name: 'Revenue',
      data: [30, 50, 25, 65, 75, 85, 90, 85, 75, 50, 25] // Example data
    }],
    chart: {
      type: 'bar',
      height: 340,
      toolbar: { show: false },
    },
    colors: ['#8A4FFF'], // Primary purple color
    plotOptions: {
      bar: {
        columnWidth: '60%',
        borderRadius: 10,
        borderRadiusApplication: 'around',
        // 1. Create the ghost/background bars
        colors: {
          backgroundBarColors: ['#F2F4F7'], 
          backgroundBarOpacity: 1,
          backgroundBarRadius: 10,
        }
      }
    },
    fill: {
      type: 'gradient',
      gradient: {
        shade: 'light',
        type: "vertical", // Top to bottom gradient
        shadeIntensity: 0.5,
        gradientToColors: ['#7539FF'], // Light purple at bottom
        inverseColors: false,
        opacityFrom: 1,
        opacityTo: 0.8,
        stops: [0, 100]
      }
    },
    dataLabels: { enabled: false },
    grid: {
      show: false, 
      padding: {
        left: -10, 
        right: -15
      },
    },
    yaxis: {
      show: false // Hides the Y-axis scale
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
        style: {
          colors: '#9E9E9E',
          fontSize: '12px'
        }
      }
    },
    tooltip: {
      enabled: true,
      custom: function({ series, seriesIndex, dataPointIndex, w }) {
        const month = w.globals.labels[dataPointIndex];
        const val = series[seriesIndex][dataPointIndex];
        // 2. Custom HTML for the floating tooltip
        return (
          '<div class="custom-tooltip p-2">' +
          '<p class="mb-1">' + month + '</p>' +
          '<strong>Revenue: $' + val + '</strong>' +
          '</div>'
        );
      }
    }
  };

	var chart = new ApexCharts(
		document.querySelector("#sales-performance"),
		options
	);

	chart.render();
}

if ($('#sales-status').length > 0) {

	var options = {
  series: [
    { name: 'Paid', data: [32] },
    { name: 'Refunded', data: [10] },
    { name: 'Cancelled', data: [4] }
  ],
  chart: {
    type: 'bar',
    height: 50,
    stacked: true, // This stacks them into one single row
    stackType: '100%', // Ensures the full bar represents the total
    toolbar: { show: false },
    sparkline: { enabled: true } // Removes axes and grid for a clean look
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: '100%', // Thickness of the progress bar
      borderRadius: 8, // Rounded corners on the ends
      borderRadiusApplication: 'end',
      borderRadiusWhenStacked: 'all',
    }
  },
  // START Colors for the gradients
  colors: ['#E2B93B', '#F27036', '#7352FF'], 
  fill: {
    type: 'gradient',
    gradient: {
      shade: 'light',
      type: "horizontal",
      shadeIntensity: 0.5,
      // END Colors for the gradients
      gradientToColors: ['#FFE5A5', '#FF9F7E', '#C599FF'], 
      opacityFrom: 1,
      opacityTo: 1,
      stops: [0, 100]
    }
  },
  stroke: {
    show: true,
    width: 2, // This creates the visible gaps between segments
    colors: ['#fff'] // Set this to your background color
  },
  tooltip: {
    enabled: true,
     marker: {
      show: false, // This hides the circle/dot in the tooltip
    },
    y: {
      formatter: function(val) {
        return val + "%"; // Shows the actual data value
      }, title: {
      formatter: (seriesName) => seriesName + ':'
    }
    }
  },
  legend: {
    show: false // We will handle the legend manually for better styling
  }
};

	var chart = new ApexCharts(
		document.querySelector("#sales-status"),
		options
	);

	chart.render();
}

//Sales Chart
if ($('#sales-growth').length > 0) {

  var options = {
    series: [{
      name: 'Sales',
      data: [10, 25, 20, 45, 40, 55, 60, 80, 100] // Values representing the wave
    }],
    chart: {
      type: 'area',
      height: 260,
      toolbar: { show: false },
      sparkline: { enabled: true } // Removes axes/grid to match the minimal look
    },
    stroke: {
      curve: 'smooth', // Creates the "wave" effect
      width: 2,
      colors: ['#E04F16'] // Primary orange line color
    },
    markers: {
      size: 0,
      colors: ['#E04F16'],      // The center dot color (must be an array)
      strokeColors: '#ffffff',  // The white outer ring
      strokeWidth: 3,           // Thickness of the white ring
      hover: {
        size: 6,                // Shows only on hover
        sizeOffset: 3
      }
    },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.7,
        opacityTo: 0, // Fades out completely at the bottom
        stops: [0, 90, 100],
        colorStops: [
          { offset: 0, color: "#E04F16", opacity: 0.7 },
          { offset: 100, color: "#FFFFFF", opacity: 0 }
        ]
      }
    },
    // Customizing the tooltip and the vertical dashed line
    tooltip: {
      enabled: true,
      x: { show: false },
      marker: { show: false },
    },
    // This creates the vertical dashed line on hover
    xaxis: {
        crosshairs: {
          show: true,
          width: 1,
          position: 'back',
          stroke: {
            color: '#EF6A3A',
            width: 1,
            dashArray: 5, // Makes it dashed
          },
        }
      },
    };
    var chart = new ApexCharts(
        document.querySelector("#sales-growth"),
        options
    );

   chart.render();
}

// JObs Chart IT Dashboard
if ($('#total-revenue-chart').length > 0) {
  var sCol = {
    chart: {
      width: '100%', // Increased width to fit all bars from screenshot
      height: 55,
      type: 'bar',
      toolbar: { show: false },
      sparkline: { enabled: true },
    },
    plotOptions: {
      bar: {
        columnWidth: '60%',
        borderRadius: 2,
        // distributed: true allows each bar to have its own color
        distributed: true,
      },
    },
    grid: {
      show: false,
      padding: {
        left: -5, // Provides room for Y-axis text
        right: -5,
        bottom: 2 // Ensures the last label isn't cut off
      }
    },
    // Your data values based on the screenshot heights
    series: [{
      name: 'Jobs',
      data: [45, 30, 70, 15, 45, 100, 45]
    }],
    // The first color is 'inactive', the 6th color (index 5) is 'active'
    colors: [
      '#E9ECEF', '#E9ECEF', '#E9ECEF', '#E9ECEF', '#E9ECEF',
      '#7539FF', // This is your active orange bar
      '#E9ECEF', '#E9ECEF', '#E9ECEF', '#E9ECEF', '#E9ECEF'
    ],
    grid: { show: false },
    xaxis: { labels: { show: false }, axisBorder: { show: false } },
    yaxis: { show: false },
    tooltip: { enabled: true },
    legend: { show: false } // Hide legend caused by 'distributed'
  };

  var chart = new ApexCharts(document.querySelector("#total-revenue-chart"), sCol);
  chart.render();
}

// Revenue
if ($('#invoice-overview').length > 0) {
  var sColStacked = {
    chart: {
      height: 410,
      type: 'bar',
      stacked: true,
      toolbar: {
        show: false,
      }
    },
    plotOptions: {
      bar: {
        horizontal: false,
        borderRadius: 10, // Gives the rounded pill look
        borderRadiusWhenStacked: 'all', 
        columnWidth: '45%' // Makes the bars thicker
      },
    },
    dataLabels: {
      enabled: false
    },
    legend: {
      show: false,
    },
    colors: ['#7539FF', '#F8F5FF'],
    series: [ 
      {
        name: 'Received', 
        data: [45, 60, 20, 75, 40, 30, 60, 50, 25, 35, 45, 55] 
      },
      {
        name: 'Outstanding', 
        data: [20, 25, 10, 20, 20, 45, 30, 25, 20, 25, 30, 25]
      }
    ],
    grid: {
      borderColor: '#E2E4E6',
      strokeDashArray: 5,
      xaxis: {
        lines: {
          show: true // Show vertical dashed lines
        }
      },
      yaxis: {
        lines: {
          show: false // Hide horizontal dashed lines
        }
      },
      padding: {
        top: 0, right: -30, bottom: 0, left: -20
      },
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      tickPlacement: 'on', // THIS fixes the vertical line alignment!
      axisBorder: {
        show: false 
      },
      axisTicks: {
        show: false 
      },
      labels: {
        style: {
          colors: '#6B7280', 
          fontSize: '13px'
        }
      }
    },
    yaxis: {
      min: 0,
      labels: { show: false } 
    },
    fill: {
      opacity: 1
    },
  }

  var chart = new ApexCharts(
    document.querySelector("#invoice-overview"),
    sColStacked
  );

  chart.render();
}


// Success rate
if ($('#success_rate_chart').length > 0) {
  var sLineArea = {
    chart: {
      height: 150, // Slightly increased to ensure tooltip doesn't get clipped
      type: 'area',
      background: 'transparent', 
      sparkline: {
        enabled: true 
      },
      toolbar: {
        show: false,
      }
    },
    colors: ['#EAB308'], // Matches the yellow/gold line
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.4, 
        opacityTo: 0.05,   
        stops: [0, 90, 100]
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 2
    },
    series: [{
      name: 'Income',
      // Updated the 7th item to 64 to match your "July 64%" screenshot
      data: [0, 25, 50, 35, 55, 85, 88, 59, 58, 83, 50, 30] 
    }],
    // We pass the months here so the tooltip knows what to display
    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    xaxis: {
      crosshairs: {
        show: true,
        position: 'back',
        stroke: {
          color: '#EE9D1ABF', // Yellow dashed line
          width: 2,
          dashArray: 5 // Makes it dashed
        }
      },
      tooltip: {
        enabled: false // Hides the default bottom x-axis label
      }
    },
    yaxis: {
      show: false
    },
    grid: {
      show: false,
      padding: { left: 0, right: 0, top: 15, bottom: 0 } 
    },
    markers: {
      size: 0, // Hidden by default
      colors: ['#FFC90AB8'],
      strokeColors: '#ffffff',
      strokeWidth: 2,
      hover: {
        size: 5 // Shows the dot on hover
      }
    },
    tooltip: {
      enabled: true,
      custom: function({series, seriesIndex, dataPointIndex, w}) {
        // Grabs the data and month for the hovered point
        var data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
        var month = w.globals.labels[dataPointIndex];
        
        // Builds the dark pill UI with forced white text on the span
        return '<div style="background-color: #0F172A; padding: 6px 14px; border-radius: 10px; font-family: inherit; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">' +
          '<span style="width: 8px; height: 8px; background-color: #EAB308; border-radius: 50%; display: inline-block;"></span>' + 
          '<span style="color: #ffffff !important;">' + month + ' ' + data + '%</span>' +
          '</div>';
      }
    }
  }

  var chart = new ApexCharts(
    document.querySelector("#success_rate_chart"),
    sLineArea
  );

  chart.render();
}

// Collected
if ($('#collected-chart').length > 0) {
  var sLineArea = {
    chart: {
      height: 50,
      type: 'area',
      sparkline: {
        enabled: true 
      }
    },
    colors: ['#22C55E'], // Success Green
    stroke: {
      curve: 'smooth',
      width: 2.5
    },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.4, 
        opacityTo: 0.05,   
        stops: [0, 100]
      }
    },
    series: [{
      name: 'Collected',
      data: [15, 86, 50, 95, 50, 45, 85, 70, 55, 25] 
    }],
    xaxis: {
      crosshairs: {
        show: true,
        stroke: {
          color: '#CBD5E1', // The grey dashed line from your screenshot
          width: 1,
          dashArray: 4
        }
      }
    },
    markers: {
      size: 0,
      colors: ['#27AE60'], 
      strokeColors: '#fff', // Success color for the marker border
      strokeWidth: 2,
      hover: {
        size: 5 
      }
    },
    tooltip: {
      enabled: true,
      theme: 'dark',
      custom: function({series, seriesIndex, dataPointIndex, w}) {
        var value = series[seriesIndex][dataPointIndex];
        // Custom HTML to make the text color the success green
        return '<div style="background: #1e293b; padding: 5px 10px; border-radius: 4px; border: none;">' +
          '<span style="color: #22C55E; font-weight: bold;">$' + value.toLocaleString() + '</span>' +
          '</div>';
      }
    }
  };

  var chart = new ApexCharts(document.querySelector("#collected-chart"), sLineArea);
  chart.render();
}

// Top Revenue Chart
if ($('#top-revenue-clients').length > 0) {
  	var options = {
    series: [{
      data: [1320, 1020, 800]
    }],
    chart: {
      type: 'bar',
      height: 350,
      toolbar: { show: false },
      sparkline: { enabled: false }
    },
    plotOptions: {
      bar: {
        distributed: true, 
        columnWidth: '85%', 
        borderRadius: 15,
        borderRadiusApplication: 'around', // Rounds both Top and Bottom
        colors: {
          backgroundBarColors: ['#F8F9FA'], 
          backgroundBarOpacity: 1,
          backgroundBarRadius: 15,
        }
      }
    },
    colors: ['#09B96D', '#24C96A', '#E9F7EF'], 
    dataLabels: {
      enabled: true,
      offsetY: 15, 
      formatter: function (val, opt) {
        return ["━", "$" + val]; 
      },
      style: {
        fontSize: '14px',
        fontWeight: 700,
        colors: ['#FFFFFF', '#334155', '#334155'] 
      }
    },
    xaxis: {
      categories: ['Robert Fox', 'Jenny Wilson', 'Jane Cooper'],
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
        show: true, // Keeps the names below the bars
        offsetY: 5,
        style: {
          colors: '#64748B',
          fontSize: '14px'
        }
      }
    },
    yaxis: {
      show: false,
      max: 1600 
    },
    grid: {
      show: false,
      padding: {
        top: 0,
        right: -50,
        bottom: 0,
        left: -10
      }
    },
    // This removes the text/legend at the very bottom
    legend: {
      show: false 
    },
    tooltip: { enabled: false }
  };

  var chart = new ApexCharts(document.querySelector("#top-revenue-clients"), options);
  chart.render();
}

// Activity Tracker
if ($('#activity_tracker').length > 0) {
  var options = {
    series: [{
      name: 'Amount',
      data: [400, 700, 550, 900, 750, 600, 450] // Matching your $400 scale
    }],
    chart: {
      type: 'line',
      height: 330,
      toolbar: { show: false },
      zoom: { enabled: false }
    },
    stroke: { width: 0 },
    markers: {
      size: 10,
      colors: ['#7C3AED'],
      strokeWidth: 0,
      hover: { size: 12 }
    },
    grid: {
      show: true,
      borderColor: '#E2E8F0',
      strokeDashArray: 6,
      xaxis: { lines: { show: true } },
      yaxis: { lines: { show: false } },
      padding: { top: 40, bottom: 10, left: 20, right: 20 }
    },
    xaxis: {
      categories: ['S','M','T','W','T','F','S'],
      axisBorder: { show: false },
      axisTicks: { show: false },
      crosshairs: {
        show: true,
        width: 45,
        position: 'back',
        opacity: 0.1,
        stroke: { width: 0 },
        fill: { type: 'solid', color: '#7C3AED' }
      },
      labels: {
        style: {
          colors: '#111827',
          fontSize: '14px',
          fontWeight: 500 , 
		  background : '#dfdfdf',
		  borderRadius: '50%',
		  padding: '5px'
        },
        offsetY: 10
      }
    },
    yaxis: { show: false },
    dataLabels: { enabled: false },

    // ✅ THE TOOLTIP DESIGN (Matches your image)
    tooltip: {
      enabled: true,
      custom: function({series, seriesIndex, dataPointIndex}) {
        const val = series[seriesIndex][dataPointIndex];
        return `
          <div style="
            background: #7C3AED; 
            color: #fff !important; 
            padding: 8px 16px; 
            border-radius: 50px !important; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 14px; 
            font-weight: 500;
            box-shadow: unset !important;
            border: none !important;	
          ">
            <span style="
              width: 8px; 
              height: 8px; 
              background: #fff !important; 
              border-radius: 50% !important; 
              display: inline-block;
			  color: #fff !important;
			  
            "></span>
            <span>Amount: $${val}</span>
          </div>
        `;
      }
    }
  };

  var chart = new ApexCharts(document.querySelector('#activity_tracker'), options);
  chart.render().then(function(){
    // Styling the X-axis labels to look like circles
    var labels = document.querySelectorAll('#activity_tracker .apexcharts-xaxis-label');
    labels.forEach(function(label){
       // Apex uses SVG for labels, so we target the parent for background styling
       label.style.fontWeight = '600';
    });
  });
}

// Financial Comparison
if ($('#financial-comparison').length > 0) {
var options = {
  // Series order matters: the first one renders behind the second one
  series: [
    {
      name: 'Last Month',
      data: [7065, 7500, 6800, 7200, 8500, 7100, 6000, 6500, 8900, 6200] // Mock data (Orange)
    },
    {
      name: 'This Month',
      data: [3500, 4800, 3200, 4000, 4500, 3800, 3900, 4200, 5000, 4500] // Mock data (Purple)
    }
  ],
  chart: {
    type: 'area',
    height: 330,
    toolbar: {
      show: false // Hides the download/zoom menu to keep it clean
    },
    fontFamily: 'Inter, sans-serif' // Matches the modern font in the screenshot
  },
  colors: ['#E04F16', '#7539FF'], // [Orange for Last Month, Purple for This Month]
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.85,
      opacityTo: 0.1,
      stops: [0, 100]
    }
  },
  dataLabels: {
    enabled: false // Hides numbers on the chart itself
  },
  stroke: {
    curve: 'smooth',
    width: 2 // Gives a subtle clean edge to the curves
  },
  grid: {
    show: true,
    borderColor: '#f1f1f1',
    strokeDashArray: 0,
    xaxis: {
      lines: { show: true } // Keeps vertical lines
    },
    yaxis: {
      lines: { show: false } // Hides horizontal lines
    }
  },
  xaxis: {
    labels: { show: false }, // Hides bottom text
    axisBorder: { show: false }, // Hides the bottom base line
    axisTicks: { 
      show: true, 
      color: '#cbd5e1', 
      height: 6 
    },
    tooltip: { enabled: false }
  },
  yaxis: {
    show: false // Hides the left side numbers and line entirely
  },
  legend: {
    show: false // Hidden in the screenshot
  },
  tooltip: {
    shared: true,
    intersect: false,
    custom: function({series, seriesIndex, dataPointIndex, w}) {
      // Fetch dynamic data based on where the user hovers
      let lastMonthVal = series[0][dataPointIndex];
      let thisMonthVal = series[1][dataPointIndex];
      
      // Custom HTML to perfectly replicate your screenshot's tooltip
      return `
        <div style="padding: 16px; border-radius: 16px; background: #ffffff; box-shadow: 0px 10px 25px rgba(0,0,0,0.1); border: 1px solid #f1f1f1;">
          <div style="font-weight: 800; font-size: 15px; margin-bottom: 12px; color: #111827;">JAN 26</div>
          <div style="display: flex; align-items: center; margin-bottom: 10px; font-size: 15px; color: #4B5563;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #8A56FD; margin-right: 10px;"></span>
            This Month : $${thisMonthVal}
          </div>
          <div style="display: flex; align-items: center; font-size: 15px; color: #4B5563;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #E66C37; margin-right: 10px;"></span>
            Last Month: $${lastMonthVal}
          </div>
        </div>
      `;
    }
  }
};

var chart = new ApexCharts(document.querySelector("#financial-comparison"), options);
chart.render();
}

// Payment Methods
if ($('#payment_methods').length > 0) {
var options = {
  series: [30, 20, 25, 25], // Proportions for the payment channels
  chart: {
    type: 'donut',
    height: 250
  },
  labels: [' Bank Transfer', 'UPI', 'Cheque', 'Credit Card'],
  colors: ['#F59E0B', '#DB2777', '#3B82F6', '#FFD700'], // Orange, Pink, Blue, Yellow
  plotOptions: {
    pie: {
      donut: {
        size: '75%', // Makes the donut ring thick
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: '14px',
			fontWeight: '500',
            color: '#000',
            offsetY: 22
          },
          value: {
            show: true,
            fontSize: '24',
            fontWeight: 700,
            color: '#000',
            offsetY: -12,
            formatter: function (val) {
              return "100%" // Displays 100% in the center
            }
          },
          total: {
            show: true,
            label: 'Bank Transfer',
            color: '#000'
          }
        }
      }
    }
  },
  dataLabels: { enabled: false },
  legend: { show: false } // Hides the legend as per your screenshot
};

var chart = new ApexCharts(document.querySelector("#payment_methods"), options);
chart.render();
}




