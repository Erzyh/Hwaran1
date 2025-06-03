<?php
require_once 'config/db.php';
require_once 'config/app.php';
include 'includes/header.php';
?>

<head>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

    <style>
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      background: linear-gradient(-45deg, #0d1b2a, #1b263b, #1b263b, #0d1b2a);
      background-size: 400% 400%;
      animation: gradient 20s ease infinite;
    }
    @keyframes gradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    #threejs-container {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 0;
    }
    #threejs-container canvas {
      filter: drop-shadow(0 0 5px rgba(255,255,255,0.5));
    }
    canvas.star-canvas {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
    }
    canvas.snow-canvas {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 2;
    }
    @media only screen and (max-width: 600px) {
      html, body {
        background-size: 300% 300%;
      }
      #threejs-container,
      canvas.star-canvas,
      canvas.snow-canvas {
        width: 100vw;
        height: 100vh;
      }
    }
    </style>
</head>

<body>
  <canvas id="stars" class="star-canvas"></canvas>
  <canvas id="snow" class="snow-canvas"></canvas>
  <div id="threejs-container"></div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
  <script>
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth/window.innerHeight, 0.1, 1000);
    camera.position.z = 250;
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    document.getElementById('threejs-container').appendChild(renderer.domElement);
    renderer.domElement.style.position = 'absolute';
    renderer.domElement.style.top = '0';
    renderer.domElement.style.left = '0';
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    renderer.domElement.style.zIndex = '0';
    
    const textParticlesGeometry = new THREE.BufferGeometry();
    const backgroundParticlesGeometry = new THREE.BufferGeometry();
    
    const textParticles = [];
    const backgroundParticles = [];
    
    const textParticlesMaterial = new THREE.PointsMaterial({
      size: 1.5,
      sizeAttenuation: true,
      vertexColors: true,
      transparent: true,
      opacity: 0.9
    });
    
    const backgroundParticlesMaterial = new THREE.PointsMaterial({
      color: 0x555555,
      size: 1,
      sizeAttenuation: true,
      transparent: true,
      opacity: 0.4,
    });
    
    const textCanvas = document.createElement('canvas');
    const ctx = textCanvas.getContext('2d');
    textCanvas.width = 600;
    textCanvas.height = 200;
    ctx.fillStyle = 'white';
    ctx.font = '70px NotoSans KR';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('HWARAN', textCanvas.width/2, textCanvas.height/2);
    
    const imageData = ctx.getImageData(0, 0, textCanvas.width, textCanvas.height).data;
    const colors = [];
    
    for(let y = 0; y < textCanvas.height; y++){
      for(let x = 0; x < textCanvas.width; x++){
        const index = (y * textCanvas.width + x) * 4;
        if(imageData[index + 3] > 100){
          const particleX = x - textCanvas.width / 2;
          const particleY = textCanvas.height / 2 - y;
          const particleZ = (Math.random() - 0.5) * 50;
          textParticles.push(particleX, particleY, particleZ);
          const baseHue = (x / textCanvas.width) * 360;
          const color = new THREE.Color(`hsl(${baseHue}, 100%, 70%)`);
          colors.push(color.r, color.g, color.b);
        }
      }
    }
    
    textParticlesGeometry.setAttribute('position', new THREE.Float32BufferAttribute(textParticles, 3));
    textParticlesGeometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
    
    for(let i = 0; i < 5000; i++){
      const x = (Math.random() - 0.5) * 1000;
      const y = (Math.random() - 0.5) * 1000;
      const z = (Math.random() - 0.5) * 1000;
      backgroundParticles.push(x, y, z);
    }
    backgroundParticlesGeometry.setAttribute('position', new THREE.Float32BufferAttribute(backgroundParticles, 3));
    
    const textPoints = new THREE.Points(textParticlesGeometry, textParticlesMaterial);
    const backgroundPoints = new THREE.Points(backgroundParticlesGeometry, backgroundParticlesMaterial);
    scene.add(textPoints);
    scene.add(backgroundPoints);
    
    function adjustTextScale() {
      const mobileBreakpoint = 600;
      if(window.innerWidth < mobileBreakpoint){
        const scale = window.innerWidth / mobileBreakpoint;
        textPoints.scale.set(scale, scale, scale);
      } else {
        textPoints.scale.set(1, 1, 1);
      }
    }
    adjustTextScale();
    
    function animate(){
      requestAnimationFrame(animate);
      const time = performance.now() / 1000;
      
      textPoints.rotation.y = 0.1 * Math.sin(time * 0.5);
      textPoints.rotation.x = 0.1 * Math.cos(time * 0.3);
      textPoints.position.z = 5 * Math.sin(time * 0.3);
      
      backgroundPoints.rotation.y += 0.001;
      
      renderer.render(scene, camera);
    }
    animate();
    
    window.addEventListener('resize', () => {
      renderer.setSize(window.innerWidth, window.innerHeight);
      camera.aspect = window.innerWidth / window.innerHeight;
      camera.updateProjectionMatrix();
      adjustTextScale();
    });
  </script>

  <script>
    const starCanvas = document.getElementById("stars");
    const starCtx = starCanvas.getContext("2d");
    starCanvas.width = window.innerWidth;
    starCanvas.height = window.innerHeight;
    
    const stars = [];
    for(let i = 0; i < 100; i++){
      stars.push({
        x: Math.random() * starCanvas.width,
        y: Math.random() * starCanvas.height,
        radius: Math.random() * 2,
        opacity: Math.random()
      });
    }
    
    function drawStars(){
      starCtx.clearRect(0, 0, starCanvas.width, starCanvas.height);
      stars.forEach(star => {
        starCtx.beginPath();
        starCtx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
        starCtx.fillStyle = `rgba(255, 255, 255, ${star.opacity})`;
        starCtx.fill();
        star.opacity += (Math.random() - 0.5) * 0.05;
        if(star.opacity < 0) star.opacity = 0;
        if(star.opacity > 1) star.opacity = 1;
      });
      requestAnimationFrame(drawStars);
    }
    drawStars();
    
    const snowCanvas = document.getElementById("snow");
    const snowCtx = snowCanvas.getContext("2d");
    snowCanvas.width = window.innerWidth;
    snowCanvas.height = window.innerHeight;
    
    let snowflakes = [];
    let groundSnow = [];
    let snowHeightMap = new Array(snowCanvas.width).fill(0);
    
    for(let i = 0; i < 200; i++){
      snowflakes.push({
        x: Math.random() * snowCanvas.width,
        y: Math.random() * snowCanvas.height,
        radius: Math.random() * 3 + 1,
        speed: Math.random() * 2 + 1
      });
    }
    
    function drawSnow(){
      snowCtx.clearRect(0, 0, snowCanvas.width, snowCanvas.height);
      
      groundSnow.forEach(snow => {
        snowCtx.beginPath();
        snowCtx.arc(snow.x, snow.y, snow.radius, 0, Math.PI * 2);
        snowCtx.fillStyle = "white";
        snowCtx.fill();
      });
      
      snowflakes.forEach((flake, index) => {
        snowCtx.beginPath();
        snowCtx.arc(flake.x, flake.y, flake.radius, 0, Math.PI * 2);
        snowCtx.fillStyle = "white";
        snowCtx.fill();
        flake.y += flake.speed;
      
        let collided = false;
        for(let snow of groundSnow){
          const dx = flake.x - snow.x;
          const dy = flake.y - snow.y;
          const distance = Math.sqrt(dx * dx + dy * dy);
          if(distance < flake.radius + snow.radius){
            collided = true;
            break;
          }
        }
        if(collided || flake.y + flake.radius >= snowCanvas.height){
          let x = flake.x;
          let y = flake.y;
          for(let i = 0; i < 10; i++){
            const leftSnow = groundSnow.find(s => Math.abs(s.x - (x - 1)) < flake.radius);
            const rightSnow = groundSnow.find(s => Math.abs(s.x - (x + 1)) < flake.radius);
            if(leftSnow && rightSnow) break;
            if(!leftSnow) x -= 1;
            else if(!rightSnow) x += 1;
          }
          groundSnow.push({ x, y: snowCanvas.height - flake.radius, radius: flake.radius });
          snowflakes.splice(index, 1);
        }
      });
      
      while(snowflakes.length < 200){
        snowflakes.push({
          x: Math.random() * snowCanvas.width,
          y: -10,
          radius: Math.random() * 3 + 1,
          speed: Math.random() * 2 + 1
        });
      }
      requestAnimationFrame(drawSnow);
    }
    drawSnow();
    
    window.addEventListener("resize", () => {
      starCanvas.width = window.innerWidth;
      starCanvas.height = window.innerHeight;
      snowCanvas.width = window.innerWidth;
      snowCanvas.height = window.innerHeight;
      snowflakes = [];
      groundSnow = [];
      snowHeightMap = new Array(snowCanvas.width).fill(0);
      for(let i = 0; i < 200; i++){
        snowflakes.push({
          x: Math.random() * snowCanvas.width,
          y: Math.random() * snowCanvas.height,
          radius: Math.random() * 3 + 1,
          speed: Math.random() * 2 + 1
        });
      }
    });
  </script>
</body>
</html>