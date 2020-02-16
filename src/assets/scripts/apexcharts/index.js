import * as $ from 'jquery';
import ApexCharts from 'apexcharts';

export default (function () {

    if (typeof window.apexcharts === 'undefined') {
        window.apexcharts = {};
    }

    window.apexcharts = {
        configs:{
        },
        selectors: {
            classes:{
            },
            ids:{
            },
            attributes: {
                dataIsApexChart  : "data-is-apex-chart",
                dataApexChartName: "data-apex-chart-name",
                dataApexChartType: "data-apex-chart-type",
                dataApexChartLabels: "data-apex-chart-labels",
                dataApexChartValues: "data-apex-chart-values",
                dataApexChartColors: "data-apex-chart-colors",
                dataApexChartXAxisValues: "data-apex-chart-x-axis-values",
                dataApexChartYAxisTitle: "data-apex-chart-y-axis-title",
                dataApexChartXAxisTitle: "data-apex-chart-x-axis-title",
                dataApexChartEnableValueLabels: "data-apex-chart-enable-value-labels",
                dataApexChartOffsetGridLeft: "data-apex-chart-offset-grid-left",
            }
        },
        charts:{
            types:{
                pie: "pie",
                line: "line",
            }
        },
        init: function () {
            let _this = this;
            let chartsToHandle = $("[" + this.selectors.attributes.dataIsApexChart + "=true]");

            $.each(chartsToHandle, function(index, chartToHandle) {
                let $chartToHandle  = $(chartToHandle);
                let options         = _this.buildOptionsForChartType($chartToHandle);

                let chart = new ApexCharts($chartToHandle[0], options);
                chart.render();
            });

        },
        /**
         * Build options for given chart type if builder is created for given type
         * @param $chartToHandle    {object}
         * @returns                 {object}
         */
        buildOptionsForChartType: function($chartToHandle){

            let chartType = $chartToHandle.attr(this.selectors.attributes.dataApexChartType);
            let options   = {};

            switch(chartType){
                case this.charts.types.pie:
                {
                    let chartLabelsJson = $chartToHandle.attr(this.selectors.attributes.dataApexChartLabels);
                    let chartValuesJson = $chartToHandle.attr(this.selectors.attributes.dataApexChartValues);

                    let chartLabelsArray = JSON.parse(chartLabelsJson);
                    let chartValuesArray = JSON.parse(chartValuesJson);

                    options = this.buildOptionsForPieChart(chartLabelsArray, chartValuesArray);
                }
                    break;

                case this.charts.types.line:
                {
                    let chartColorsJson      = $chartToHandle.attr(this.selectors.attributes.dataApexChartColors);
                    let chartXAxisValuesJson = $chartToHandle.attr(this.selectors.attributes.dataApexChartXAxisValues);
                    let chartDataSetsJson    = $chartToHandle.attr(this.selectors.attributes.dataApexChartValues);

                    let yAxisTitle           = $chartToHandle.attr(this.selectors.attributes.dataApexChartYAxisTitle);
                    let xAxisTitle           = $chartToHandle.attr(this.selectors.attributes.dataApexChartXAxisTitle);

                    let enableValuesLabels   = ("true" === $chartToHandle.attr(this.selectors.attributes.dataApexChartEnableValueLabels));

                    let dataApexChartOffsetGridLeft   = $chartToHandle.attr(this.selectors.attributes.dataApexChartOffsetGridLeft);
                    dataApexChartOffsetGridLeft       = ("undefined" === typeof dataApexChartOffsetGridLeft ? 0 : parseInt(dataApexChartOffsetGridLeft));

                    let chartColorsArray      = JSON.parse(chartColorsJson);
                    let chartValuesArray      = JSON.parse(chartDataSetsJson);
                    let chartXAxisValuesArray = JSON.parse(chartXAxisValuesJson);

                    options = this.buildOptionsForLineChart(chartXAxisValuesArray, chartValuesArray, chartColorsArray, yAxisTitle, xAxisTitle, enableValuesLabels, dataApexChartOffsetGridLeft);
                }
                    break;

                default:
                    throw({
                        "message"   : "Unsupported chart type for building options",
                        "chartType" : chartType
                    })
            }

            return options;
        },
        /**
         * Build options needed to create pie type chart
         * @param chartValuesArray      {array}
         * @param chartLabelsArray      {array}
         * @returns                     {object}
         */
        buildOptionsForPieChart: function(chartLabelsArray, chartValuesArray) {

            chartValuesArray = this.normalizeChartValues(chartValuesArray);

            let _this = this;
            let options = {
                series: chartValuesArray,
                chart: {
                    width: 380,
                    type: _this.charts.types.pie,
                },
                labels: chartLabelsArray,
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

            return options;
        },
        /**
         * Build options needed to create line type chart
         * @param xAxisValuesArray          {   array}
         * @param chartDataSetsArray            {array}
         * @param chartColorsArray              {array}
         * @param yAxisTitle                    {string}
         * @param xAxisTitle                    {string}
         * @param enableValuesLabels            {bool}
         * @param dataApexChartOffsetGridLeft   {int}
         * @returns                             {object}
         */
        buildOptionsForLineChart: function(xAxisValuesArray, chartDataSetsArray, chartColorsArray, yAxisTitle, xAxisTitle, enableValuesLabels, dataApexChartOffsetGridLeft){

            let _this          = this;
            let series         = [];
            let maxValueInSets = 0;

            $.each(chartDataSetsArray, function(dataSetName, dataSetValues){

                dataSetValues = _this.normalizeChartValues(dataSetValues);

                let seriesData = {
                    name: dataSetName,
                    data: dataSetValues
                };

                $.each(dataSetValues, function(index, value){
                    if( value > maxValueInSets ){
                        maxValueInSets = value;
                    }
                });

                series.push(seriesData);
            });

            var options = {
                chart: {
                    height: 350,
                    type: this.charts.types.line,
                    stacked: false
                },
                grid: {
                    padding: {
                        left: dataApexChartOffsetGridLeft, // this is padding of entire chart zone from the axis Y
                    }
                },
                dataLabels: {
                    enabled: enableValuesLabels
                },
                colors: chartColorsArray,
                series: series,
                stroke: {
                    width: [4, 4]
                },
                plotOptions: {
                    bar: {
                        columnWidth: "20%"
                    }
                },
                markers: {
                    size: 4
                },
                xaxis: {
                    title: {
                        text: xAxisTitle
                    },
                    categories: xAxisValuesArray
                },
                yaxis: {
                    title: {
                        text: yAxisTitle
                    },
                    min: 0,
                    max: Math.ceil((maxValueInSets + 1/3 * maxValueInSets))
                },
                tooltip: {
                    shared: false,
                    intersect: true,
                    x: {
                        show: false
                    }
                },
                legend: {
                    horizontalAlign: "center",
                    offsetY: 0,
                    offsetX: -5
                }
            };

            return options;
        },

        /**
         * Values must be numeric but from front these always come as string so this function changes string numbers to floats
         * @param chartValuesArray  {array}
         * @return                  {array}
         */
        normalizeChartValues: function(chartValuesArray){
            let normalizedValuesArray = [];

            $.each(chartValuesArray, function(index, value){
                let normalizedValue = parseFloat(value);
                normalizedValuesArray.push(normalizedValue);
            });

            return normalizedValuesArray;
        },
        reinit: function () {
        },
    };

}())
