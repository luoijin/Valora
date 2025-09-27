<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Project</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>

    <?php include 'pages/navbar.php'; ?>

    <img class="image-gradient" src="gradient.png" alt="gradient">
    <div class="layer-blur"></div>

    <div class="container">
        
        <main>
            <div class="content">
                <div data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="300" data-aos-offset="0" data-aos-duration="1500" class="tag-box">
                    <div class="tag">ANNE LORAINE PARDILLO</div>
                </div>

                <h1 data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="300" data-aos-offset="0" data-aos-duration="2000">Hi, it's Loraine<br>I'm a Developer</h1>
                <p data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="300" data-aos-offset="0" data-aos-duration="2500" class="description">
                    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Excepturi incidunt nisi, sequi laboriosam ullam odio adipisci magni distinctio, quo iste esse amet optio quae facilis pariatur veritatis asperiores corrupti! Et.
                </p>

                <div data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="300" data-aos-offset="0" data-aos-duration="3000" class="buttons">
                    <a href="#" class="btn-get-started">Learn more &gt;</a>
                   

                </div>
            </div>
        </main>

        <spline-viewer data-aos="fade-zoom-in" data-aos-easing="ease-in-back" data-aos-delay="300" data-aos-offset="0" data-aos-duration="3000" class="robot-3d" url="https://prod.spline.design/Atn1aW6BMUimYRhU/scene.splinecode"></spline-viewer>
    </div>

    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.10.64/build/spline-viewer.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>

    <script src="navbar.js"></script>

        



</body>
</html>