<?php
require_once __DIR__ . '/../admin/includes/CMS.php';
CMS::getHeader();
?>
<style>
    .about-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 3rem; align-items: flex-start; }
    @media (max-width: 992px) { .about-grid { grid-template-columns: 1fr; gap: 2rem; } }
    .room-gallery { width: 100%; min-width: 0; }
    .room-description { width: 100%; min-width: 0; }
    .room-gallery-main { border-radius: 4px; overflow: hidden; border: 8px solid white; box-shadow: 10px 10px 0px var(--border); margin-bottom: 2rem; cursor: zoom-in; aspect-ratio: 4 / 3; position: relative; max-height: 450px; z-index: 100; width: 100%; }
    .room-gallery-main img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.5s ease; }
    .thumb-slider-container { position: relative; display: flex; align-items: center; margin-top: 1rem; gap: 10px; width: 100%; overflow: hidden; min-width: 0; }
    .thumb-scroll { display: flex; gap: 10px; overflow-x: hidden; scroll-behavior: smooth; flex-grow: 1; padding: 5px 0; min-width: 0; }
    .thumb-scroll img { flex: 0 0 100px; width: 100px; height: 75px; object-fit: cover; border-radius: 4px; border: 2px solid white; cursor: pointer; opacity: 0.7; transition: 0.2s; flex-shrink: 0; }
    .thumb-scroll img.active { opacity: 1; border-color: var(--primary) !important; }
    .slider-nav { background: white; border: 1px solid var(--border); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 200; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); color: var(--primary); flex-shrink: 0; }
</style>

    <!-- Hero Section -->
    <section class="hero" id="home" style="height: 60vh; min-height: 400px;">
        <div class="hero-bg-slider" id="hero-bg-slider">
            <div class="hero-bg-slide active" style="background-image: url('/assets/img/babiccin/babiccin-apartman-222753.jpg');"></div>
            <div class="hero-bg-slide" style="background-image: url('/assets/img/babiccin/babiccin-apartman-222749.jpg');"></div>
            <div class="hero-bg-slide" style="background-image: url('/assets/img/babiccin/babiccin-apartman-222757.jpg');"></div>
        </div>
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h2 class="hero-subtitle fadeIn">Apartmán</h2>
            <h1 class="hero-title fadeInDelay">Babiččin apartmán</h1>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="about-grid">
                <div class="reveal" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div class="room-gallery-main" id="main-gallery-trigger">
                            <img src="assets/img/babiccin/babiccin-apartman-222753.jpg" alt="Babiččin apartmán - Interiér" id="main-gallery-img">
                        </div>
                        <div class="thumb-slider-container">
                            <button class="slider-nav" id="prev-thumb"><i data-lucide="chevron-left"></i></button>
                            <div class="thumb-scroll" id="thumb-scroll">
                                <img src="assets/img/babiccin/babiccin-apartman-222753.jpg" class="active">
                                <img src="assets/img/babiccin/babiccin-apartman-222749.jpg">
                                <img src="assets/img/babiccin/babiccin-apartman-222757.jpg">
                                <img src="assets/img/babiccin/babiccin-apartman-222808.jpg">
                                <img src="assets/img/babiccin/babiccin-apartman-222816.jpg">
                                <img src="assets/img/babiccin/babiccin-apartman-222825.jpg">
                                <img src="assets/img/babiccin/babiccin-apartman-222832.jpg">
                            </div>
                            <button class="slider-nav" id="next-thumb"><i data-lucide="chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="reveal" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <span class="section-tag">Tradiční styl</span>
                        <h2 class="section-title">Útulné ubytování s atmosférou</h2>
                        <p>Babiččin apartmán je ideální volbou pro ty, kteří hledají klid a tradiční venkovskou atmosféru s moderním komfortem. Tento prostorný apartmá je pro 4 osoby. Disponuje plně vybavenou kuchyní s varnou deskou, troubou, lednicí, koupelnou se sprchou a jednou ložnicí s manželskou postelí a dvěma jednolůžky. K dispozici je také TV s plochou obrazovkou a bezplatné Wi-Fi připojení. K ubytování je možnost objednat si snídani.</p>

                        <ul class="features-list" style="margin-top: 2rem;">
                            <li><i data-lucide="users"></i> Kapacita: 4 osoby</li>
                            <li><i data-lucide="bed"></i> 1x samostatná ložnice</li>
                            <li><i data-lucide="circle-parking"></i> Parkování zdarma</li>
                        </ul>
                    </div>

                    <div style="margin-top: auto; padding-top: 1.5rem;">
                        <div style="font-family: 'Libre Baskerville'; font-size: 1.8rem; color: var(--primary); margin-bottom: 0.2rem;">
                            1 500 Kč / noc
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; font-style: italic;">
                            * Při rezervaci pouze na 1 noc +500 Kč servisní poplatek k ceně.
                        </div>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                            <a href="index.php#rooms" class="btn" title="Zpět na přehled apartmánů" aria-label="Zpět na přehled apartmánů" style="display: inline-flex; align-items: center; justify-content: center; background: transparent !important; border: 2px solid var(--primary); color: var(--primary); font-weight: 600; padding: 0.8rem;">
                                <i data-lucide="arrow-left" style="width: 20px; height: 20px; color: var(--primary);"></i>
                            </a>
                            <a href="#poptat-termin" class="btn btn-primary">Rezervovat</a>
                            <button type="button" class="btn btn-outline-primary open-room-calendar" data-room="babiccin" data-room-name="Babiččin apartmán">
                                <i data-lucide="calendar"></i> Obsazenost
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="details-grid reveal-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; margin-top: 4rem;">
                <div class="detail-card" style="background: white; padding: 3rem; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid var(--border);">
                    <h3 style="font-family: 'Libre Baskerville', serif; font-size: 1.6rem; color: var(--primary); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.8rem; border-bottom: 2px solid var(--border); padding-bottom: 1rem;">
                        <i data-lucide="package-check"></i> Vybavení pokoje
                    </h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.2rem;">
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="utensils" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Plně vybavená kuchyň
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="bed" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> 1 ložnice (1x manželská postel, 2x jednolůžko)
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="bath" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Koupelna se sprchovým koutem
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="tv" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Televize a Wi-Fi
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="refrigerator" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Lednice s mrazákem
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="flame" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Varná deska
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="chef-hat" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Trouba
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="coffee" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Rychlovarná konvice
                        </li>
                        <li style="display: flex; align-items: center; gap: 1rem; color: var(--text-dark); font-weight: 500; padding: 0.2rem 0;">
                            <i data-lucide="wind" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Fén
                        </li>
                    </ul>
                </div>

                <div class="detail-card" style="background: linear-gradient(145deg, #FAF7F2, #F3EFE6); padding: 3rem; border-radius: 8px; box-shadow: 0 15px 35px rgba(139, 94, 60, 0.12); border: 1px solid rgba(139, 94, 60, 0.2); border-top: 5px solid var(--primary);">
                    <h3 style="font-family: 'Libre Baskerville', serif; font-size: 1.6rem; color: var(--text-dark); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.8rem; border-bottom: 2px solid rgba(139, 94, 60, 0.15); padding-bottom: 1rem;">
                        <span style="background: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(139,94,60,0.15); color: var(--primary);"><i data-lucide="concierge-bell" style="width: 22px; height: 22px;"></i></span> Doplňkové služby
                    </h3>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 1.2rem;">
                        <li style="display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); font-weight: 500; border-bottom: 1px dotted rgba(139, 94, 60, 0.25); padding-bottom: 0.8rem;">
                            <span style="display: flex; align-items: center; gap: 1rem;"><i data-lucide="coffee" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Snídaně</span>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(139,94,60,0.3);">250 Kč</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); font-weight: 500; border-bottom: 1px dotted rgba(139, 94, 60, 0.25); padding-bottom: 0.8rem;">
                            <span style="display: flex; align-items: center; gap: 1rem;"><i data-lucide="dog" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Příplatek za psa</span>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(139,94,60,0.3);">400 Kč / noc</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); font-weight: 500; border-bottom: 1px dotted rgba(139, 94, 60, 0.25); padding-bottom: 0.8rem;">
                            <span style="display: flex; align-items: center; gap: 1rem;"><i data-lucide="droplet" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Ručník</span>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(139,94,60,0.3);">50 Kč</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); font-weight: 500; border-bottom: 1px dotted rgba(139, 94, 60, 0.25); padding-bottom: 0.8rem;">
                            <span style="display: flex; align-items: center; gap: 1rem;"><i data-lucide="bike" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Půjčovna kol</span>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(139,94,60,0.3);">300 Kč / den</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); font-weight: 500; border-bottom: 1px dotted rgba(139, 94, 60, 0.25); padding-bottom: 0.8rem;">
                            <span style="display: flex; align-items: center; gap: 1rem;"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M4 17l2-7 4-6h5l2 3h3a2 2 0 0 1 2 2v2a3 3 0 0 1-3 3h-3l-2 5H7l-3-2z"/><circle cx="15" cy="8" r="1"/><path d="M10 4c2 1 3 3 3 5"/></svg> Jízda na koni</span>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(139,94,60,0.3);">od 400 Kč</span>
                        </li>
                        <li style="display: flex; justify-content: space-between; align-items: center; color: var(--text-dark); font-weight: 500; border-bottom: 1px dotted rgba(139, 94, 60, 0.25); padding-bottom: 0.8rem;">
                            <span style="display: flex; align-items: center; gap: 1rem;"><i data-lucide="baby" style="color: var(--primary); width: 22px; height: 22px; flex-shrink: 0;"></i> Dětská postýlka</span>
                            <span style="background: var(--primary); color: white; padding: 0.25rem 0.8rem; border-radius: 20px; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 5px rgba(139,94,60,0.3);">100 Kč / noc</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Reservation Section -->
    <section class="contact section-padding bg-white" id="poptat-termin">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info reveal">
                    <span class="section-tag">Rezervace</span>
                    <h2 class="section-title">Poptat termín</h2>
                    <p>Zaujal vás náš Babiččin apartmán? Pošlete nám nezávaznou poptávku a my se vám ozveme s nejlepší cenou.</p>
                    <div class="info-item" style="margin-top: 2rem;">
                        <i data-lucide="mail"></i>
                        <div>
                            <h4>Email</h4>
                            <p>info@statekstranovice.cz</p>
                        </div>
                    </div>
                    <div class="info-item" style="margin-top: 1rem;">
                        <i data-lucide="phone"></i>
                        <div>
                            <h4>Telefon</h4>
                            <p>+420 737 887 985</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper reveal-up">
                    <form class="contact-form">
                        <input type="hidden" name="room" value="Babiččin apartmán">
                        <div class="form-row">
                            <div class="form-group"><label>Příjezd</label><input type="date" name="prijezd" required></div>
                            <div class="form-group"><label>Odjezd</label><input type="date" name="odjezd" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Počet hostů</label>
                                <select name="pocet_hostu">
                                    <option>1-2 osoby</option>
                                    <option>3 osoby</option>
                                    <option>4 osoby</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Vaše jméno</label><input type="text" name="jmeno" placeholder="Jan Novák" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>E-mail</label><input type="email" name="email" placeholder="jan.novak@email.cz" required></div>
                            <div class="form-group"><label>Telefon</label><input type="tel" name="telefon" placeholder="+420 123 456 789"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Odeslat poptávku</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php CMS::getFooter(); ?>
