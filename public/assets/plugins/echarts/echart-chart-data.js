
if ($('#map-chart').length > 0) {
	const apiUsageData = [
		{ name: 'United States', value: [-100.00, 40.00, 45000] },
		{ name: 'United Kingdom', value: [-2.00, 54.00, 34000] },
		{ name: 'Australia', value: [133.00, -25.00, 12600] },
		{ name: 'Denmark', value: [9.50, 56.00, 8900] },
		{ name: 'France', value: [2.21, 46.22, 3400] }
	];

	function initializeChart() {
		const chartDom = document.getElementById('map-chart');
		const myChart = echarts.init(chartDom);

		const patternCanvas = document.createElement('canvas');
		const pCtx = patternCanvas.getContext('2d');
		patternCanvas.width = 8; 
		patternCanvas.height = 8;

		// 2. Draw the dot on the virtual canvas
		pCtx.fillStyle = '#D1D5DB'; 
		pCtx.beginPath();
		pCtx.arc(2, 2, 1.5, 0, Math.PI * 2); 
		pCtx.fill();

		const option = {
			tooltip: { trigger: 'item' },
			visualMap: {
				min: 0,
				max: 100,
				dimension: 2,
				show: false, // Set 'show' to false to hide the sidebar
				inRange: {
					symbolSize: [10]
				}
			},
			geo: {
				map: 'world', // This now works because world.js was loaded above
				roam: true,
				itemStyle: {
					areaColor: '#F0F0F0',
					borderColor: '#FFFFFF'
				}
			},
			series: [{
				type: 'scatter',
				coordinateSystem: 'geo',
				data: apiUsageData,
				symbol: 'pin', // Use 'pin' or 'diamond' for the marker style
        		symbolSize: 20,
				itemStyle: {
					color: '#7F56FF' // The purple brand color
				},
				label: {
					show: true,
					position: 'bottom',
					// Formats the value as $45,000
					formatter: (params) => `$${params.value[2].toLocaleString()}`,
					backgroundColor: '#7F56FF',
					color: '#fff',
					padding: [4, 8],
					borderRadius: 4,
					fontSize: 12,
					distance: 10
				}
			}]
		};

		myChart.setOption(option);

		// This makes the chart responsive
		window.addEventListener('resize', function () {
			myChart.resize();
		});
	}

	initializeChart();
}
