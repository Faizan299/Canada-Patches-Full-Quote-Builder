<?php
/*
Plugin Name: Canada Patches Full Quote Builder
Description: Summary Bar Fixed with JS Scroll + Real-time Name/Email
Version: 3.9
Author: Faizan Yaseen
*/

add_shortcode('patch_quote_builder', 'pqb_render_full_final');

function pqb_render_full_final() {
    ob_start();

    if (isset($_POST['submit_quote'])) {
        $admin_email = get_option('admin_email');
        $full_name = sanitize_text_field($_POST['Full_Name']);
        $subject = "New Quote Request from: " . $full_name;
        
        $body = "<h2 style='color:#e31e24; font-family: Arial;'>Order Details</h2>";
        $body .= "<table style='width:100%; border-collapse: collapse; font-family: Arial;'>";
        
        foreach ($_POST as $key => $value) {
            if ($key != 'submit_quote' && !empty($value)) {
                $clean_key = ucwords(str_replace(['Selected_', '_'], ' ', $key));
                $body .= "<tr style='border-bottom: 1px solid #eee;'><td style='padding:10px; font-weight:bold;'>$clean_key:</td><td style='padding:10px;'>".sanitize_text_field($value)."</td></tr>";
            }
        }

        if (!empty($_FILES['Artwork']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $uploadedfile = $_FILES['Artwork'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $body .= "<tr style='border-bottom: 1px solid #eee;'><td style='padding:10px; font-weight:bold;'>Artwork:</td><td style='padding:10px;'><a href='".$movefile['url']."'>Download Artwork File</a></td></tr>";
            }
        }

        $body .= "</table>";
        wp_mail($admin_email, $subject, $body, array('Content-Type: text/html; charset=UTF-8'));
        echo "<div style='padding:15px; background:#d4edda; color:#155724; border-radius:4px; margin-bottom:20px; font-family:sans-serif;'>Success! Your quote request has been sent.</div>";
    }
    ?>

    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Oswald:wght@500;600&display=swap" rel="stylesheet">
    
    <style>
        :root { --red: #e31e24; --bg: #ffffff; --border: #ddd; --oswald: 'Oswald', sans-serif; --jost: 'Jost', sans-serif; }
        
        .main-wrapper { 
            display: flex; 
            gap: 30px; 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
            font-family: var(--jost); 
            line-height: 1.4; 
            color: #333; 
            align-items: flex-start;
        }

        .form-area { flex: 2; width: 100%; margin-bottom:5%; }
        
        .step-title { font-family: var(--oswald); font-size: 23px; color: black; margin: 30px 0 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; text-transform: uppercase; font-weight:500; }
        
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .card { border: 1px solid var(--border); padding: 12px; text-align: center; cursor: pointer; transition: 0.2s; border-radius: 4px; background: #fff; }
        .card:hover { border-color: var(--red); }
        .card.active { border: 2px solid var(--red); background: #fff5f5; }
        .card img { width: 100%; height: 75px; object-fit: contain; margin-bottom: 8px; pointer-events: none; }
        .card p { margin: 0; font-size: 16px; font-weight: 600; color: black; pointer-events: none; }
        .card small { font-size: 10px; color: #888; display: block; margin-top: 4px; pointer-events: none; }

        .input-row { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 20px; }
        .field-group { flex: 1; }
        .field-group label { display: block; font-size: 12px; margin-bottom: 5px; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 4px; font-family: var(--jost); }
        
        .delivery-row { display: flex; gap: 15px; margin-bottom: 20px; }
        .delivery-btn { border: 1px solid var(--border); padding: 15px; flex: 1; text-align: center; cursor: pointer; border-radius: 4px; background: #f9f9f9; }
        .delivery-btn.active { border: 2px solid var(--red); background: #fff; }
        .delivery-btn strong { display: block; font-size: 14px; }
        .delivery-btn span { font-size: 12px; color: #666; }

        .account-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px; }
        .full-width { grid-column: span 2; }

        /* SIDEBAR CONTAINER */
        .sidebar-container { flex: 1; position: relative; min-width: 300px; }
        
        .summary-box { 
            background: #eaedef; 
            padding: 25px; 
            border-radius: 8px; 
            border-top: 6px solid var(--red); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            width: 100%;
        }

        .sticky-summary {
            position: fixed;
            top: 20px;
            width: inherit; 
            max-width: 370px; 
        }

        .summary-box h3 { font-family: var(--oswald); font-size: 23px; margin: 0 0 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .summary-line { display: flex; justify-content: space-between; margin-bottom: 13px; border-bottom: 1px dashed #bbb; padding-bottom: 10px; font-size: 13px; }
        .summary-line span { color: var(--red); font-weight: 600; text-align: right; }
        
        .submit-btn { background: var(--red); color: white; border: none; padding: 18px; font-family: var(--oswald); font-size: 20px; cursor: pointer; margin-top: 20px; border-radius: 4px; text-transform: uppercase; }

        @media (max-width: 900px) { 
            .main-wrapper { flex-direction: column; } 
            .grid { grid-template-columns: repeat(2, 1fr); } 
            .sidebar-container { width: 100%; min-width: unset; }
            .sticky-summary { position: relative !important; top: 0 !important; width: 100% !important; max-width: unset !important; }
        }
    </style>

    <form method="post" enctype="multipart/form-data">
        <div class="main-wrapper">
            <div class="form-area">
               <div class="step-title">Step 1. Select Patch Type</div>
<div class="grid" data-group="Patch_Type">
    <?php 
    $patches = [
        "Embroidered Patches"          => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/Embroidered-Patches.webp",
        "Chenille Patches"             => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/Chenille-Badges.webp",
        "PVC / Rubber Patches"         => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/PVC-Rubber-Badges.webp",
        "Embroidered 3D Puff"          => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/embroidered-3d-puff.webp",
        "Woven Patches"                => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/Woven-Badges.webp",
        "Leather Patches"              => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/leather-badges.webp",
        "Printed / Sublimated Patches" => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/sublimation-badges.webp",
        "Silicon Badges"               => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/silicone-badges.webp",
        "Bullion Badges"               => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/Bullion-Badges.webp",
        "TPU Badges"                   => "https://customembroiderypatches.ca/wp-content/uploads/2025/12/tpu-badges.webp",
        "Other Type"                   => "" // Yahan image link khali chor dein
    ];

    foreach($patches as $name => $img): ?>
        <div class="card" onclick="selectMe(this, 'Patch_Type')" <?php if($name == "Other Type") echo 'style="display: flex; flex-direction: column; justify-content: center; min-height: 125px;"'; ?>>
            
            <?php if(!empty($img)): ?>
                <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>">
            <?php endif; ?>

            <p style="<?php if(empty($img)) echo 'margin: 0; font-size: 18px;'; ?>"><?php echo $name; ?></p>
            
        </div>
    <?php endforeach; ?>
</div>

                <div class="step-title">Step 2. Customize Your Order</div>
                <div class="input-row">
                    <div class="field-group"><label>Unit</label><select name="Selected_Unit" id="unit" onchange="sync()"><option>Inches</option><option>Centimeter</option><option>Millimeter</option></select></div>
                    <div class="field-group"><label>Height</label><input type="text" name="Selected_Height" id="h" oninput="sync()"></div>
                    <div class="field-group"><label>Width</label><input type="text" name="Selected_Width" id="w" oninput="sync()"></div>
                </div>

                <div class="delivery-row" data-group="Delivery">
                    <div class="delivery-btn" onclick="selectMe(this, 'Delivery')"><strong>Standard Delivery</strong><span>20 to 30 Days</span></div>
                    <div class="delivery-btn" onclick="selectMe(this, 'Delivery')"><strong>Express Delivery</strong><span>10 to 14 Days</span></div>
                </div>

                <div class="step-title">Embroidery Coverage</div>
                <div class="grid" data-group="Coverage">
                    <div class="card" onclick="selectMe(this, 'Coverage')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/50-Coverage.webp"><p>50% Coverage</p><small>50% embroidery</small></div>
                    <div class="card" onclick="selectMe(this, 'Coverage')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/75-Coverage.webp"><p>75% Coverage</p><small>75% surface</small></div>
                    <div class="card" onclick="selectMe(this, 'Coverage')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/100-Coverage.webp"><p>100% Coverage</p><small>Full surface</small></div>
                </div>

                <div class="step-title">Required Quantity</div>
                <div class="grid" data-group="Quantity">
                    <?php foreach([50, 100, 250, 500, 1000] as $q) echo "<div class='card' onclick=\"selectMe(this, 'Quantity')\"><p>$q Pcs</p></div>"; ?>
                    <div class="card" onclick="selectMe(this, 'Quantity', true)"><p>Custom Quantity</p></div>
                </div>
                <input type="number" name="Custom_Qty" id="customQtyInput" placeholder="Enter quantity" style="display:none; margin-bottom:15px;" oninput="updateCustomQty(this.value)">

                <div class="step-title">Step 3. Select Backing Type</div>
                <div class="grid" data-group="Backing">
                    <div class="card" onclick="selectMe(this, 'Backing')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/heat-seal.webp"><p>Heat Seal</p></div>
                    <div class="card" onclick="selectMe(this, 'Backing')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/non-woven.webp"><p>Non-Woven</p></div>
                    <div class="card" onclick="selectMe(this, 'Backing')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/hook-side.webp"><p>Velcro Hook & Loop</p></div>
                    <div class="card" onclick="selectMe(this, 'Backing')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/plastic.webp"><p>Plastic</p></div>
                    <div class="card" onclick="selectMe(this, 'Backing')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/sew-on.webp"><p>Sew On</p></div>
                    <div class="card" onclick="selectMe(this, 'Backing')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/peel-stick.webp"><p>Peel-and-Stick</p></div>
                </div>

                <div class="step-title">Select Border Type</div>
                <div class="grid" data-group="Border">
                    <div class="card" onclick="selectMe(this, 'Border')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/border-merrow.webp"><p>Merrow Border</p></div>
                    <div class="card" onclick="selectMe(this, 'Border')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/border-satin.webp"><p>Satin Border</p></div>
                    <div class="card" onclick="selectMe(this, 'Border')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/border-laser.webp"><p>Laser Cut</p></div>
                </div>

                <div class="step-title">Select Thread</div>
                <div class="grid" data-group="Thread">
                    <div class="card" onclick="selectMe(this, 'Thread')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/thread-normal.webp"><p>Normal Thread</p></div>
                    <div class="card" onclick="selectMe(this, 'Thread')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/thread-metallic.webp"><p>Metallic Thread</p></div>
                    <div class="card" onclick="selectMe(this, 'Thread')"><img src="https://customembroiderypatches.ca/wp-content/uploads/2025/12/thread-madeira.webp"><p>Madeira Thread</p></div>
                </div>

                <div class="step-title">Account Details</div>
                <div class="account-grid">
                    <div><input type="text" name="Full_Name" id="f_name" placeholder="Full Name" required oninput="sync()"></div>
                    <div><input type="email" name="Email_Address" id="f_email" placeholder="Email Address" required oninput="sync()"></div>
                    <div class="full-width"><textarea name="Notes" rows="3" placeholder="Notes..."></textarea></div>
                    <div class="full-width">
                        <p style="font-size:14px; margin-bottom:5px;">Upload Artwork:</p>
                        <input type="file" name="Artwork" id="artworkFile">
                    </div>
                </div>

                <button type="submit" name="submit_quote" class="submit-btn">GET INSTANT QUOTE</button>

                <input type="hidden" name="Selected_Patch" id="h_Patch_Type">
                <input type="hidden" name="Selected_Delivery" id="h_Delivery">
                <input type="hidden" name="Selected_Coverage" id="h_Coverage">
                <input type="hidden" name="Selected_Quantity" id="h_Quantity">
                <input type="hidden" name="Selected_Backing" id="h_Backing">
                <input type="hidden" name="Selected_Border" id="h_Border">
                <input type="hidden" name="Selected_Thread" id="h_Thread">
            </div>

            <div class="sidebar-container" id="sidebarContainer">
                <div class="summary-box" id="summaryBox">
                    <h3>Order Summary</h3>
                    <div class="summary-line"><strong>Name:</strong> <span id="s_Name">---</span></div>
                    <div class="summary-line"><strong>Email:</strong> <span id="s_Email">---</span></div>
<!--                     <hr style="border:0; border-top:1px solid #ccc; margin-bottom:15px;"> -->
                    
                    <div class="summary-line"><strong>Service:</strong> <span id="s_Patch_Type">---</span></div>
                    <div class="summary-line"><strong>Size:</strong> <span id="s_Size">---</span></div>
                    <div class="summary-line"><strong>Delivery:</strong> <span id="s_Delivery">---</span></div>
                    <div class="summary-line"><strong>Coverage:</strong> <span id="s_Coverage">---</span></div>
                    <div class="summary-line"><strong>Quantity:</strong> <span id="s_Quantity">---</span></div>
                    <div class="summary-line"><strong>Backing:</strong> <span id="s_Backing">---</span></div>
                    <div class="summary-line"><strong>Border:</strong> <span id="s_Border">---</span></div>
                    <div class="summary-line"><strong>Thread:</strong> <span id="s_Thread">---</span></div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // --- IMPROVED STICKY LOGIC ---
        window.addEventListener('scroll', function() {
            const summary = document.getElementById('summaryBox');
            const container = document.getElementById('sidebarContainer');
            const wrapper = document.querySelector('.main-wrapper');
            
            if (window.innerWidth > 900) {
                const rect = container.getBoundingClientRect();
                const wrapperRect = wrapper.getBoundingClientRect();
                
                if (rect.top < -50) { 
                    summary.classList.add('sticky-summary');
                    summary.style.width = container.offsetWidth + "px";
                    
                    let topGap = 180; 

                    if (wrapperRect.bottom < (summary.offsetHeight + topGap + 20)) {
                        summary.style.position = "absolute";
                        summary.style.top = "auto";
                        summary.style.bottom = "0";
                    } else {
                        summary.style.position = "fixed";
                        summary.style.top = topGap + "px";
                        summary.style.bottom = "auto";
                    }
                } else {
                    summary.classList.remove('sticky-summary');
                    summary.style.position = "relative";
                    summary.style.top = "0";
                    summary.style.width = "100%";
                }
            }
        });

        function selectMe(el, group, isCustom = false) {
            el.parentElement.querySelectorAll('.card, .delivery-btn').forEach(item => item.classList.remove('active'));
            el.classList.add('active');
            let val = el.querySelector('p') ? el.querySelector('p').innerText : el.querySelector('strong').innerText;
            if (group === 'Quantity') {
                document.getElementById('customQtyInput').style.display = isCustom ? 'block' : 'none';
                if (isCustom) val = document.getElementById('customQtyInput').value || "Custom";
            }
            document.getElementById('h_' + group).value = val;
            document.getElementById('s_' + group).innerText = val;
        }

        function updateCustomQty(val) {
            document.getElementById('h_Quantity').value = val + " Pcs";
            document.getElementById('s_Quantity').innerText = val + " Pcs";
        }

        function sync() {
            // Size Sync
            const h = document.getElementById('h').value || '0';
            const w = document.getElementById('w').value || '0';
            const u = document.getElementById('unit').value;
            document.getElementById('s_Size').innerText = h + ' x ' + w + ' ' + u;

            // Name & Email Sync
            const nameVal = document.getElementById('f_name').value || '---';
            const emailVal = document.getElementById('f_email').value || '---';
            document.getElementById('s_Name').innerText = nameVal;
            document.getElementById('s_Email').innerText = emailVal;
        }
    </script>    

    <?php
    return ob_get_clean();
}