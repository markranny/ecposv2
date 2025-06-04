// Sample data organized by month
var data = {
    January: 50,
    February: 70,
    March: 60,
    April: 80,
    May: 65,
    June: 75,
    July: 85,
    August: 90,
    September: 70,
    October: 80,
    November: 85,
    December: 95
};

// Get the canvas element
var ctx = document.getElementById('hiringratechart').getContext('2d');

// Create the bar chart
var hiringratechart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: Object.keys(data), // Extracting month names as labels
        datasets: [{
            label: 'Data per Month',
            data: Object.values(data), // Extracting values for each month
            backgroundColor: 'rgba(54, 162, 235, 0.5)', // Blue color for bars
            borderColor: 'rgba(54, 162, 235, 1)', // Border color for bars
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Hiring Rate Per Month'
            },
            legend: {
                display: false
            }
        }
    }
});