import React from 'react';
import ReactDOM from 'react-dom';

import JqxChart from '../../../jqwidgets-react/react_jqxchart.js';

class App extends React.Component {
    render() {
        let sampleData = [
            { Country: 'Switzerland', Inflation2012: -0.95, Inflation2011: -0.72 },
            { Country: 'USA', Inflation2012: 2.35, Inflation2011: 2.96 },
            { Country: 'Germany', Inflation2012: 2.03, Inflation2011: 2.10 },
            { Country: 'India', Inflation2012: 8.38, Inflation2011: 6.49 },
            { Country: 'China', Inflation2012: 3.34, Inflation2011: 4.06 },
            { Country: 'Canada', Inflation2012: 2.05, Inflation2011: 2.30 }];

        let padding = { left: 20, top: 5, right: 20, bottom: 5 };

        let titlePadding = { left: 10, top: 0, right: 0, bottom: 10 };

        let xAxis =
            {
                dataField: 'Country',
                gridLines: {
                    visible: true,
                    dashStyle: '2,2',
                    color: 'grey'
                },
                tickMarks: {
                    dashStyle: '2,2',
                    color: 'grey'
                }
            };

        let seriesGroups =
            [
                {
                    type: 'column',
                    orientation: 'horizontal',
                    columnsGapPercent: 50,
                    valueAxis:
                    {
                        visible: true,
                        minValue: -5,
                        maxValue: 10,
                        unitInterval: 1,
                        labels: {
                            formatSettings: { sufix: '%' }
                        },
                        gridLines: {
                            dashStyle: '2,2',
                            color: 'grey'
                        },
                        tickMarks:
                        {
                            dashStyle: '2,2',
                            color: 'grey'
                        }
                    },
                    toolTipFormatSettings: { sufix: '%' },
                    series: [
                        { dataField: 'Inflation2012', displayText: 'Inflation 2012' },
                        { dataField: 'Inflation2011', displayText: 'Inflation 2011' }
                    ]
                }
            ];
        return (
            <JqxChart style={{ width: 850, height: 500 }}
                title={'CPI inflation comparison by country'} description={'Years: 2011 vs 2012'}
                showLegend={true} enableAnimations={true} padding={padding}
                titlePadding={titlePadding} source={sampleData} xAxis={xAxis}
                colorScheme={'scheme02'} seriesGroups={seriesGroups}
            />
        )
    }
}

ReactDOM.render(<App />, document.getElementById('app'));
