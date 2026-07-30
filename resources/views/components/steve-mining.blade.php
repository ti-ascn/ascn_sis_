@props(['clean' => false])

@if($clean)
    <div {{ $attributes->merge(['class' => 'steve-mining-container select-none']) }}>
        <div class="steve-scene">
            <!-- Steve -->
            <div class="steve-character">
                <div class="steve-pants-pixel"></div>
                <div class="steve-torso-pixel"></div>
                <div class="steve-head-pixel"></div>
                <div class="steve-arm-mining">
                    <div class="steve-pickaxe-pixel"></div>
                </div>
            </div>
            
            <!-- Block -->
            <div class="mine-block-pixel"></div>
            
            <!-- Particles -->
            <div class="mine-particles">
                <div class="mine-particle mine-p1"></div>
                <div class="mine-particle mine-particle-blue mine-p2"></div>
                <div class="mine-particle mine-p3"></div>
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2.5 bg-gray-50/50 dark:bg-gray-800/30 border border-gray-200/50 dark:border-gray-700/50 rounded-xl px-3 py-1 shadow-sm select-none']) }}>
        <div class="steve-mining-container">
            <div class="steve-scene">
                <!-- Steve -->
                <div class="steve-character">
                    <div class="steve-pants-pixel"></div>
                    <div class="steve-torso-pixel"></div>
                    <div class="steve-head-pixel"></div>
                    <div class="steve-arm-mining">
                        <div class="steve-pickaxe-pixel"></div>
                    </div>
                </div>
                
                <!-- Block -->
                <div class="mine-block-pixel"></div>
                
                <!-- Particles -->
                <div class="mine-particles">
                    <div class="mine-particle mine-p1"></div>
                    <div class="mine-particle mine-particle-blue mine-p2"></div>
                    <div class="mine-particle mine-p3"></div>
                </div>
            </div>
        </div>
    </div>
@endif

<style>
    /* Steve Mining Scene Styles */
    .steve-mining-container {
        position: relative;
        width: 80px;
        height: 40px;
        overflow: hidden;
        background: transparent;
        display: inline-block;
    }
    .steve-scene {
        position: absolute;
        bottom: 2px;
        left: 5px;
        width: 70px;
        height: 36px;
    }
    .steve-character {
        position: absolute;
        bottom: 0;
        left: 5px;
        width: 20px;
        height: 32px;
    }
    .steve-head-pixel {
        position: absolute;
        top: 0;
        left: 3px;
        width: 10px;
        height: 10px;
        background: #db9c72;
        border-top: 2px solid #583b27;
        border-left: 1.5px solid #583b27;
        box-shadow: inset 1px -1px 0 rgba(0,0,0,0.1);
    }
    .steve-head-pixel::after {
        content: '';
        position: absolute;
        bottom: 2px;
        right: 1px;
        width: 2px;
        height: 2px;
        background: #0000ff;
        box-shadow: -1px 0.5px 0 #fff;
    }
    .steve-torso-pixel {
        position: absolute;
        top: 10px;
        left: 3px;
        width: 10px;
        height: 12px;
        background: #00a8a8;
        box-shadow: inset -1.5px -1.5px 0 rgba(0,0,0,0.15);
    }
    .steve-pants-pixel {
        position: absolute;
        bottom: 0;
        left: 3px;
        width: 10px;
        height: 10px;
        background: #2444b5;
        box-shadow: inset -1.5px -1.5px 0 rgba(0,0,0,0.2);
    }
    .steve-arm-mining {
        position: absolute;
        top: 11px;
        right: -3px;
        width: 10px;
        height: 6px;
        background: #db9c72;
        border-left: 4px solid #00a8a8;
        transform-origin: left center;
        animation: steve-strike 0.55s infinite ease-in-out;
    }
    .steve-pickaxe-pixel {
        position: absolute;
        top: -7px;
        right: -7px;
        width: 15px;
        height: 15px;
        background: transparent;
        transform: rotate(45deg);
    }
    .steve-pickaxe-pixel::before {
        content: '';
        position: absolute;
        top: 3px;
        left: 7px;
        width: 1.5px;
        height: 11px;
        background: #805c36;
    }
    .steve-pickaxe-pixel::after {
        content: '';
        position: absolute;
        top: 1.5px;
        left: 1.5px;
        width: 11px;
        height: 3px;
        background: #50e4e4;
        border-radius: 1px;
        box-shadow: 0 0 3px rgba(80, 228, 228, 0.6);
    }
    .mine-block-pixel {
        position: absolute;
        bottom: 0;
        right: 8px;
        width: 16px;
        height: 16px;
        background: #737373;
        border: 1px solid #525252;
        box-shadow: inset -1.5px -1.5px 0 rgba(0,0,0,0.2);
        animation: block-shake 0.55s infinite ease-in-out;
    }
    .mine-block-pixel::before {
        content: '';
        position: absolute;
        top: 2px;
        left: 3px;
        width: 2.5px;
        height: 2.5px;
        background: #50e4e4;
        box-shadow: 6px 2px 0 #50e4e4, 3px 6px 0 #50e4e4, 7px 8px 0 #50e4e4;
    }
    .mine-particles {
        position: absolute;
        bottom: 10px;
        right: 18px;
        width: 14px;
        height: 14px;
        pointer-events: none;
    }
    .mine-particle {
        position: absolute;
        width: 1.5px;
        height: 1.5px;
        background: #737373;
    }
    .mine-particle-blue {
        background: #50e4e4;
    }
    .mine-p1 {
        top: 3px; left: 3px;
        animation: particle-fly-1 0.55s infinite linear;
    }
    .mine-p2 {
        top: 1px; left: 6px;
        animation: particle-fly-2 0.55s infinite linear;
    }
    .mine-p3 {
        top: 5px; left: 5px;
        animation: particle-fly-3 0.55s infinite linear;
    }
    @keyframes steve-strike {
        0%, 100% { transform: rotate(-35deg); }
        50% { transform: rotate(15deg); }
    }
    @keyframes block-shake {
        0%, 40%, 100% { transform: scale(1) translateY(0); }
        50% { transform: scale(0.93) translateY(1px); }
    }
    @keyframes particle-fly-1 {
        0% { transform: translate(0, 0) scale(1); opacity: 1; }
        100% { transform: translate(-10px, -6px) scale(0.5); opacity: 0; }
    }
    @keyframes particle-fly-2 {
        0% { transform: translate(0, 0) scale(1); opacity: 1; }
        100% { transform: translate(-3px, -11px) scale(0.5); opacity: 0; }
    }
    @keyframes particle-fly-3 {
        0% { transform: translate(0, 0) scale(1); opacity: 1; }
        100% { transform: translate(-5px, -8px) scale(0.5); opacity: 0; }
    }
</style>
