// Get the canvas element
var ctx = document.getElementById('tardinesschart').getContext('2d');

// Create the pie chart
var tardinesschart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Red', 'Green'],
        datasets: [{
            label: '# of Votes',
            data: [12, 2],
            backgroundColor: [
                'rgba(255, 99, 132, 0.5)',
                'rgba(75, 192, 192, 0.5)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Avg. Tardiness'
            }
        }
    }
});