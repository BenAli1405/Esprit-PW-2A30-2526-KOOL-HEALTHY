<div class="card-panel">
  <div class="section-header">
    <h3 class="no-margin">Statistiques des calories brûlées</h3>
    <p>Évolution des calories brûlées sur les 4 dernières semaines</p>
  </div>

  <?php if (empty($caloriesPerWeek)): ?>
    <p>Aucune donnée disponible pour les statistiques.</p>
  <?php else: ?>
    <canvas id="caloriesChart" width="400" height="200"></canvas>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('caloriesChart').getContext('2d');
        const caloriesChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: <?php echo json_encode(array_keys($caloriesPerWeek)); ?>,
            datasets: [{
              label: 'Calories brûlées',
              data: <?php echo json_encode(array_values($caloriesPerWeek)); ?>,
              backgroundColor: 'rgba(54, 162, 235, 0.5)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Calories'
                }
              },
              x: {
                title: {
                  display: true,
                  text: 'Semaine'
                }
              }
            },
            plugins: {
              legend: {
                display: true,
                position: 'top'
              }
            }
          }
        });
      });
    </script>
  <?php endif; ?>
</div>