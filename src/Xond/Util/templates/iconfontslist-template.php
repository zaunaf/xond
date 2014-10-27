<!DOCTYPE html>
<html>
<head>
  <title>Ionicons v1.5.2 Cheatsheet</title>
  <style>
  * {
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
  }

  body {
    background: #fff;
    color: #444;
    font: 16px/1.5 "Helvetica Neue", Helvetica, Arial, sans-serif;
  }

  a, a:visited {
    color: #888;
    text-decoration: underline;
  }
  a:hover, a:focus { color: #000; }

  header {
    border-bottom: 2px solid #ddd;
    margin-bottom: 20px;
    overflow: hidden;
    padding: 20px 0;
  }

  header h1 {
    color: #888;
    float: left;
    font-size: 36px;
    font-weight: 300;
  }

  header a {
    float: right;
    font-size: 14px;
  }

  .container {
    margin: 0 auto;
    max-width: 1200px;
    min-width: 960px;
    padding: 0 20px;
    width: 95%;
  }

  .icon-row {
    border-bottom: 1px dotted #ccc;
    padding: 10px 0 20px;
    margin-bottom: 20px;
  }
  .ion {
    -webkit-touch-callout: text;
    -webkit-user-select: text;
    -khtml-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
  }

  .preview-icon { vertical-align: bottom; }

  .preview-scale {
    color: #888;
    font-size: 12px;
    margin-top: 5px;
  }

  .step {
    display: inline-block;
    line-height: 1;
    position: relative;
    width: 10%;
  }

  .step i {
    -webkit-transition: opacity .3s;
    -moz-transition: opacity .3s;
    -ms-transition: opacity .3s;
    -o-transition: opacity .3s;
    transition: opacity .3s;
  }

  .step:hover i { opacity: .3; }

  .size-12 { font-size: 12px; }
  .size-14 { font-size: 14px; }
  .size-16 { font-size: 16px; }
  .size-18 { font-size: 18px; }
  .size-21 { font-size: 21px; }
  .size-24 { font-size: 24px; }
  .size-32 { font-size: 32px; }
  .size-48 { font-size: 48px; }
  .size-64 { font-size: 64px; }
  .size-96 { font-size: 96px; }

  .usage { margin-top: 10px; }
  .usage input {
    font-family: monospace;
    margin-right: 3px;
    padding: 2px 5px;
    text-align: center;
    -webkit-touch-callout: text;
    -webkit-user-select: text;
    -khtml-user-select: text;
    -moz-user-select: text;
    -ms-user-select: text;
    user-select: text;
  }

  .usage label { 
    font-size: 12px; 
    text-align: right; 
    padding: 0 3px 0 60px;
  }
  .usage label:first-child { padding-left: 0; }
  .usage .name { width: 180px; }
  .usage .html { width: 80px; }
  .usage .css { width: 80px; }

  footer {
    color: #888;
    font-size: 12px;
    padding: 20px 0;
  }
  </style>
  <link href="css/ionicons.css" rel="stylesheet" type="text/css" />
</head>

<body>
  <div class="container">
    <header>
      <h1>Ionicons v1.5.2 Cheatsheet, 601 icons:</h1>
      <p><a href="http://ionicons.com/">Ionicons Homepage</a></p>
    </header>
    <div class="content">
      <div class="icon-row">
        <div class="preview-icon">
          <span class="step size-12">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-14">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-16">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-18">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-21">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-24">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-32">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-48">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-64">
            <i class="icon ion-alert"></i>
          </span>
          <span class="step size-96">
            <i class="icon ion-alert"></i>
          </span>
        </div>
        <div class="preview-scale"><span class="step">12</span>
          <span class="step">14</span>
          <span class="step">16</span>
          <span class="step">18</span>
          <span class="step">21</span>
          <span class="step">24</span>
          <span class="step">32</span>
          <span class="step">48</span>
          <span class="step">64</span>
          <span class="step">96</span></div>
          <div class="usage">
            <label>Classname:</label>
            <input class="name" type="text" readonly="readonly" onClick="this.select();" value="ion-alert" />

            <label>Selectable:</label>
            <span class="ion">&#xf101;</span>

            <label>Escaped HTML:</label>
            <input class="html" type="text" readonly="readonly" onClick="this.select();" value="&amp;#xf101;" />

            <label>CSS Content:</label>
            <input class="css" type="text" readonly="readonly" onClick="this.select();" value="\f101" />
          </div>
        </div>
    </div>
    <footer>
      Made with love by the <a href="http://ionicframework.com/">Ionic Framework</a>
    </footer>
  </div>
</body>
</html>