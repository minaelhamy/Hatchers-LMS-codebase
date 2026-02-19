@layout('views/layouts/master')

@section('content')
    @if (customCompute($featured_image))
        <div class="featured-slider">
            <img src="{{ base_url('uploads/gallery/' . $featured_image->file_name) }}"
                alt="{{ $featured_image->file_alt_text }}">
        </div>
    @else
        <div class="bradcam-area area-padding">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="section-title white-title bradcam-title text-uppercase text-center">
                            <h2> {{ $page->title }} </h2>
                            <span class="star"></span>
                            <span class="star"></span>
                            <span class="star"></span>
                        </div>
                    </div>
                    <div class="bradcam-wrap text-center">
                        <nav class="bradcam-inner">
                            <a class="bradcam-item text-uppercase"
                                href="{{ base_url('frontend/' . $homepageType . '/' . $homepage->url) }}">{{ $homepageTitle }}</a>
                            <span class="brd-separetor">/</span>
                            <span class="bradcam-item active text-uppercase">{{ $page->title }}</span>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <section id="about" class="about-area area-padding">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    {{ htmlspecialchars_decode($page->content ?? "") }}
                </div>
            </div>
        </div>
    </section>
@endsection
