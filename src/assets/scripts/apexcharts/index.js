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
                dataApexChartValues: "data-apex-chart-values"
            }
        },
        charts:{
            types:{
                pie: "pie"
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
         * @param $chartToHandle {object}
         * @returns {object}
         */
        buildOptionsForChartType: function($chartToHandle){

            let chartType = $chartToHandle.attr(this.selectors.attributes.dataApexChartType);
            let options   = {};

            switch(chartType){
                case this.charts.types.pie:
                    let chartLabelsJson = $chartToHandle.attr(this.selectors.attributes.dataApexChartLabels);
                    let chartValuesJson = $chartToHandle.attr(this.selectors.attributes.dataApexChartValues);

                    let chartLabelsArray = JSON.parse(chartLabelsJson);
                    let chartValuesArray = JSON.parse(chartValuesJson);

                    chartValuesArray = this.normalizeChartValues(chartValuesArray);
                    options          = this.buildOptionsForPieChart(chartLabelsArray, chartValuesArray);
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
         * @param chartValuesArray
         * @param chartLabelsArray
         * @returns {object}
         */
        buildOptionsForPieChart: function(chartLabelsArray, chartValuesArray) {
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
         * Values must be numeric but from front these always come as string so this function changes string numbers to floats
         * @param chartValuesArray {array}
         * @return {array}
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
