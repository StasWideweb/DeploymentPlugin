document.addEventListener('DOMContentLoaded', () => {
  const deploymentInfoElement = document.getElementById('deploymentInfo');
  const deploymentDetails = document.getElementById('deploymentDetails');
  const statusMessage = document.getElementById('statusMessage');
  const deployForm = document.getElementById('deployForm');
  const deployButton = document.getElementById('deployButton');
  const stepsContainer = document.getElementById('deployment-steps');
  if (deploymentInfo){
    let interval;
    let previousDeploymentData = {}; // Хранение предыдущего состояния

    deployForm.addEventListener('submit', function (event) {
      event.preventDefault();

      if (deployButton) {
        deployButton.disabled = true;
      }

      fetch(event.target.action, {
        method: 'POST',
        body: new FormData(event.target),
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            statusMessage.innerText = 'Deployment started successfully!';
            fetchLastDeployment();
          } else {
            statusMessage.innerText = `Error: ${data.error || 'Unknown error'}`;
          }
          deployButton.disabled = false;
        })
        .catch(error => {
          statusMessage.innerText = `Error: ${error.message}`;
          deployButton.disabled = false;
        });
    });

    async function fetchLastDeployment() {
      try {
        const response = await fetch('/actions/deployment/deployment/get-last-deployment');
        const data = await response.json();

        if (data.success) {
          const deployment = data.lastDeployment;
          const deploymentId = deployment.identifier;
          const deploymentStatus = deployment.status.toLowerCase();

          deploymentInfoElement.innerHTML = `
            <h3>Last Deploy Information</h3>
            <p><strong>ID:</strong> ${deploymentId}</p>
            <p><strong>Status:</strong> ${deploymentStatus}</p>
            <p><strong>Author:</strong> ${deployment.deployer}</p>
            <p><strong>Date:</strong> ${new Date(deployment.timestamps.started_at).toLocaleString()}</p>
          `;

          if (deploymentStatus === 'running') {
            deploymentDetails.style.display = 'block';
            checkDeploymentStatus(deploymentId);
          } else {
            deploymentDetails.style.display = 'none';
          }
        } else {
          deploymentInfoElement.innerHTML = `<p>Error: ${data.error}</p>`;
        }
      } catch (error) {
        deploymentInfoElement.innerHTML = `<p>There is no information about the last deployment.</p>`;
      }
    }

    function checkDeploymentStatus(deploymentId) {
      const statusUrl = `/actions/deployment/deployment/status?deploymentId=${deploymentId}`;

      deploymentDetails.style.display = 'block';
      interval = setInterval(() => {
        fetch(statusUrl)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const deploymentResult = data;
              const currentStages = deploymentResult.stages;

              if (JSON.stringify(previousDeploymentData) !== JSON.stringify(deploymentResult)) {
                updateStages(currentStages);
                previousDeploymentData = { ...deploymentResult }; // Сохранение текущего состояния
              }

              statusMessage.innerText = deploymentResult.status;

              if (deploymentResult.status.toLowerCase() !== 'running') {
                clearInterval(interval);
                statusMessage.innerText = `Deployment completed with status: ${deploymentResult.status}`;
              }
            } else {
              clearInterval(interval);
              statusMessage.innerText = data.error || 'Failed to fetch status.';
            }
          })
          .catch(error => {
            clearInterval(interval);
            statusMessage.innerText = `Error: ${error.message}`;
          });
      }, 2000);
    }

    function updateStages(stages) {
      const stagesMap = new Map();

      stages.forEach(stage => {
        const existingStage = stagesMap.get(stage.stage) || [];
        stagesMap.set(stage.stage, [...existingStage, stage]);
      });

      stepsContainer.innerHTML = '';

      stagesMap.forEach((steps, stageName) => {
        const stageDiv = document.createElement('div');
        stageDiv.className = 'stage';

        const status = getStageStatus(steps);
        stageDiv.innerHTML = `
          <div class="stage-header">
            <h3>${stageName.charAt(0).toUpperCase() + stageName.slice(1)}</h3>
            <span class="status-indicator ${status}" style="display: inline-block; margin-left: 10px;">
                        <span class="status ${status}" style="display: inline-block;"></span>
                    </span>
          </div>
          <ul class="stage-steps">
            ${steps
          .map(
            step => `
              <li class="step status--${status}">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span>${step.description}</span>
                  <span class="status-indicator status--${status}" style="margin-left: 10px;">
                    ${step.status}
                  </span>
                </div>
              </li>
            `
          )
          .join('')}
          </ul>
        `;

        stepsContainer.appendChild(stageDiv);
      });
    }

    function getStageStatus(stage) {
      const statuses = stage.map(s => s.status.toLowerCase());
      if (statuses.includes('failed')) return 'red';
      if (statuses.includes('running')) return 'blue';
      if (statuses.every(status => status === 'completed')) return 'green';
      return 'yellow';
    }

    fetchLastDeployment();
  }
});

document.addEventListener("DOMContentLoaded", function() {
  let tabs = document.querySelectorAll(".tab-nav a");
  let contents = document.querySelectorAll(".tab-content");

  tabs.forEach(tab => {
    tab.addEventListener("click", function(event) {
      event.preventDefault();

      tabs.forEach(t => t.parentElement.classList.remove("active"));
      contents.forEach(c => c.classList.remove("active"));

      this.parentElement.classList.add("active");
      document.querySelector(this.getAttribute("href")).classList.add("active");
    });
  });
});