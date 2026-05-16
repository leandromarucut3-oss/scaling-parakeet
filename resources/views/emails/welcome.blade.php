<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Morrisons Investment Welcome</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: #dfe3e8;
      padding: 30px 15px;
      color: #ffffff;
    }

    .wrapper {
      max-width: 620px;
      margin: auto;
      background: linear-gradient(180deg, #102844 0%, #1b3f66 100%);
      overflow: hidden;
      position: relative;
      box-shadow: 0 18px 45px rgba(0,0,0,0.18);
    }

    .top-logo {
      text-align: center;
      background: #f4f6f9;
      padding: 18px 20px;
      color: #12395d;
      font-size: 34px;
      font-weight: 800;
      letter-spacing: 1px;
    }

    .hero {
      padding: 55px 48px 30px;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: "";
      position: absolute;
      width: 420px;
      height: 420px;
      background: rgba(255,255,255,0.03);
      border-radius: 50%;
      left: -180px;
      bottom: -220px;
    }

    .hero::after {
      content: "";
      position: absolute;
      width: 220px;
      height: 220px;
      background: rgba(146, 214, 78, 0.08);
      border-radius: 50%;
      right: -60px;
      top: -80px;
    }

    .headline {
      position: relative;
      z-index: 2;
      max-width: 420px;
      line-height: 1.15;
      font-size: 50px;
      font-weight: 800;
      letter-spacing: -2px;
      margin-bottom: 18px;
    }

    .headline span {
      color: #a8ef6d;
    }

    .subtext {
      position: relative;
      z-index: 2;
      max-width: 420px;
      font-size: 16px;
      line-height: 1.7;
      color: rgba(255,255,255,0.82);
    }

    .growth-tag {
      position: relative;
      z-index: 2;
      margin-top: 28px;
      display: inline-block;
      background: rgba(168,239,109,0.14);
      border: 1px solid rgba(168,239,109,0.25);
      color: #d6ffb4;
      padding: 12px 20px;
      border-radius: 999px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .content-card {
      background: #ffffff;
      margin: 28px 40px 42px;
      border-radius: 26px;
      padding: 38px 34px;
      color: #1f2937;
      position: relative;
      z-index: 5;
    }

    .intro {
      font-size: 16px;
      line-height: 1.8;
      color: #374151;
      margin-bottom: 30px;
    }

    .feature {
      display: flex;
      align-items: flex-start;
      gap: 18px;
      margin-bottom: 28px;
    }

    .icon {
      min-width: 64px;
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: linear-gradient(135deg, #dff5c6, #c4eb9d);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }

    .feature h3 {
      font-size: 17px;
      margin-bottom: 8px;
      color: #102844;
    }

    .feature p {
      font-size: 14px;
      line-height: 1.7;
      color: #4b5563;
    }

    .cta {
      margin-top: 14px;
      background: linear-gradient(135deg, #102844, #1b4f7a);
      border-radius: 18px;
      padding: 24px;
      color: #ffffff;
      text-align: center;
    }

    .cta h2 {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .cta p {
      font-size: 14px;
      line-height: 1.7;
      color: rgba(255,255,255,0.84);
      margin-bottom: 20px;
    }

    .button {
      display: inline-block;
      background: #a8ef6d;
      color: #102844;
      text-decoration: none;
      font-weight: 700;
      padding: 14px 28px;
      border-radius: 999px;
      font-size: 14px;
    }

    .footer {
      text-align: center;
      padding: 0 30px 40px;
      font-size: 13px;
      color: rgba(255,255,255,0.72);
      line-height: 1.7;
    }

    @media (max-width: 640px) {
      .hero {
        padding: 42px 26px 22px;
      }

      .headline {
        font-size: 38px;
      }

      .content-card {
        margin: 22px 18px 32px;
        padding: 28px 22px;
      }

      .feature {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

  <div class="wrapper">

    <div class="top-logo">
      MORRISONS
    </div>

    <section class="hero">
      <div class="headline">
        <span>Welcome</span> To Your Future Wealth Journey
      </div>

      <div class="subtext">
        Start growing your money through trusted financial opportunities designed to help you achieve long-term success with confidence and security.
      </div>

      <div class="growth-tag">
        WELCOME • GROW CONSISTENTLY • BUILD WEALTH
      </div>
    </section>

    <div class="content-card">

      <div class="intro">
        Welcome to Morrisons, {{ $user->name }}. Congratulations on taking the first step toward financial growth. Here are important benefits and reminders to help maximize your wealth-building journey.
      </div>

      <div class="feature">
        <div class="icon">📈</div>

        <div>
          <h3>Grow Your Funds</h3>
          <p>
            Access investment opportunities designed to steadily increase your funds and support your long-term financial goals.
          </p>
        </div>
      </div>

      <div class="feature">
        <div class="icon">🛡️</div>

        <div>
          <h3>Secure Financial Management</h3>
          <p>
            Your investments are managed through secure and professionally monitored systems focused on reliability and transparency.
          </p>
        </div>
      </div>

      <div class="feature">
        <div class="icon">💹</div>

        <div>
          <h3>Track Your Growth</h3>
          <p>
            Monitor your investment growth, projected returns, and portfolio performance with clear and accessible reporting.
          </p>
        </div>
      </div>

      <div class="feature" style="margin-bottom: 0;">
        <div class="icon">🌍</div>

        <div>
          <h3>Build Long-Term Wealth</h3>
          <p>
            Create stronger financial stability by investing consistently and allowing your money to grow over time.
          </p>
        </div>
      </div>

      <div class="cta">
        <h2>Start Growing Your Wealth Today</h2>

        <p>
          Discover financial opportunities tailored for your future with Morrisons Commercial & General Merchandise Co.
        </p>

        <a href="{{ $appUrl }}" class="button">VIEW YOUR PORTFOLIO</a>
      </div>

    </div>

    <div class="footer">
      Morrisons Commercial & General Merchandise Co.<br>
      Helping individuals build stronger financial futures through strategic financial opportunities.
    </div>

  </div>

</body>
</html>
