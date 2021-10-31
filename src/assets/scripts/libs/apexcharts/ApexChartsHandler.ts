import * as $     from 'jquery';
// @ts-ignore
var ApexCharts = require('apexcharts');

/**
 * @link https://www.npmjs.com/package/apexcharts
 */
export default class ApexChartsHandler {

    /**
     * @type Object
     */
    public static selectors = {
        attributes: {
            dataIsApexChart                : "data-is-apex-chart",
            dataApexChartName              : "data-apex-chart-name",
            dataApexChartType              : "data-apex-chart-type",
            dataApexChartLabels            : "data-apex-chart-labels",
            dataApexChartValues            : "data-apex-chart-values",
            dataApexChartColors            : "data-apex-chart-colors",
            dataApexChartXAxisValues       : "data-apex-chart-x-axis-values",
            dataApexChartYAxisTitle        : "data-apex-chart-y-axis-title",
            dataApexChartXAxisTitle        : "data-apex-chart-x-axis-title",
            dataApexChartEnableValueLabels : "data-apex-chart-enable-value-labels",
            dataApexChartOffsetGridLeft    : "data-apex-chart-offset-grid-left",
        }
    };

    /**
     * @type Object
     */
    public static charts = {
        types:{
            pie  : "pie",
            line : "line",
        }
    };

    /**
     * @description Main initialization logic
     *
     */
    public init(): void
    {
        let _this = this;
        let chartsToHandle = $("[" + ApexChartsHandler.selectors.attributes.dataIsApexChart + "=true]");

        $.each(chartsToHandle, function(index, chartToHandle) {
            let $chartToHandle  = $(chartToHandle);
            let options         = _this.buildOptionsForChartType($chartToHandle);

            let chart = new ApexCharts($chartToHandle[0], options);
            chart.render();
        });

    };

    /**
     * @description Build options for given chart type if builder is created for given type
     *
     * @param $chartToHandle    {object}
     * @returns                 {object}
     */
    private buildOptionsForChartType($chartToHandle: JQuery): Object
    {

        let chartType = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartType);
        let options   = {};

        switch(chartType){
            case ApexChartsHandler.charts.types.pie:
            {
                let chartColorsJson = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartColors);
                let chartLabelsJson = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartLabels);
                let chartValuesJson = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartValues);

                let chartLabelsArray = JSON.parse(chartLabelsJson);
                let chartValuesArray = JSON.parse(chartValuesJson);
                let chartColorsArray = JSON.parse(chartColorsJson);

                options = this.buildOptionsForPieChart(chartLabelsArray, chartValuesArray, chartColorsArray);
            }
                break;

            case ApexChartsHandler.charts.types.line:
            {
                let chartColorsJson      = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartColors);
                let chartXAxisValuesJson = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartXAxisValues);
                let chartDataSetsJson    = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartValues);

                let yAxisTitle           = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartYAxisTitle);
                let xAxisTitle           = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartXAxisTitle);

                let enableValuesLabels   = ("true" === $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartEnableValueLabels));

                let dataApexChartOffsetGridLeft        = $chartToHandle.attr(ApexChartsHandler.selectors.attributes.dataApexChartOffsetGridLeft);
                let dataApexChartOffsetGridLeftNumeric = ("undefined" === typeof dataApexChartOffsetGridLeft ? 0 : parseInt(dataApexChartOffsetGridLeft));

                let chartColorsArray      = JSON.parse(chartColorsJson);
                let chartValuesArray      = JSON.parse(chartDataSetsJson);
                let chartXAxisValuesArray = JSON.parse(chartXAxisValuesJson);

                options = this.buildOptionsForLineChart(chartXAxisValuesArray, chartValuesArray, chartColorsArray, yAxisTitle, xAxisTitle, enableValuesLabels, dataApexChartOffsetGridLeftNumeric);
            }
                break;

            default:
                throw({
                    "message"   : "Unsupported chart type for building options",
                    "chartType" : chartType
                })
        }

        return options;
    };

    /**
     * @description Build options needed to create pie type chart
     *
     * @param chartValuesArray      {array}
     * @param chartLabelsArray      {array}
     * @param chartColorsArray      {array}
     * @returns                     {object}
     */
    private buildOptionsForPieChart(chartLabelsArray: Array<any>, chartValuesArray: Array<string>, chartColorsArray: Array <any>,): Object
    {

       let normalizedChartValuesArray = this.normalizeChartValues(chartValuesArray);

        let options = {
            series: normalizedChartValuesArray,
            chart: {
                width: 380,
                type: ApexChartsHandler.charts.types.pie,
            },
            labels: chartLabelsArray,
            colors: chartColorsArray,
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
    };
    /**
     * @description Build options needed to create line type chart
     *
     * @param xAxisValuesArray              {array}
     * @param chartDataSetsArray            {array}
     * @param chartColorsArray              {array}
     * @param yAxisTitle                    {string}
     * @param xAxisTitle                    {string}
     * @param enableValuesLabels            {bool}
     * @param dataApexChartOffsetGridLeft   {int}
     * @returns                             {object}
     */
    private buildOptionsForLineChart(
        xAxisValuesArray            : Array <any>,
        chartDataSetsArray          : Array <any>,
        chartColorsArray            : Array <any>,
        yAxisTitle                  : String,
        xAxisTitle                  : String,
        enableValuesLabels          : boolean,
        dataApexChartOffsetGridLeft : Object
    ): Object
    {

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

        let options = {
            chart: {
                height: 350,
                type: ApexChartsHandler.charts.types.line,
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
            legend: {
                horizontalAlign: "center",
                offsetY: 0,
                offsetX: -5
            }
        };

        return options;
    };

    /**
     * @description Values must be numeric but from front these always come as string so this function changes string numbers to floats
     *
     * @param chartValuesArray  {array}
     * @return                  {array}
     */
    private normalizeChartValues(chartValuesArray): Array<number>
    {
        let normalizedValuesArray = [];

        $.each(chartValuesArray, function(index, value){
            let normalizedValue = parseFloat(value);
            normalizedValuesArray.push(normalizedValue);
        });

        return normalizedValuesArray;
    };

}