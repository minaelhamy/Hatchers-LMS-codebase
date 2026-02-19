
    @if (!empty(frontendData::get_frontend('announcement_section_text')))
    <div class="header-top-area hidden-xs">
        <div class="">
            <div style="color:#fa5e01; padding: 4px 0px; background-color:#0c1028;position:relative; z-index:10;">
                <marquee  direction="left">
                            <a href="{{ frontendData::get_frontend('announcement_section_link') }}"
                                class="" style="color:#fff; font-weight:400; font-size:18px;">{{ frontendData::get_frontend('announcement_section_text') }}</a>
                </marquee>
            </div>
        </div>
    </div>
    @endif


            
