Vue.use(VueRate);
Vue.use(vueMoment);

var Chrome = VueColor.Chrome;
Vue.component('colorpicker', {
    components: {
        'chrome-picker': Chrome,
    },
    template: `
<div class="input-group color-picker" ref="colorpicker">
	<input type="text" class="form-control" v-model="colorValue" @focus="showPicker()" @input="updateFromInput" />
	<span class="input-group-addon color-picker-container">
		<span class="current-color" :style="'background-color: ' + colorValue" @click="togglePicker()"></span>
		<chrome-picker :value="colors" @input="updateFromPicker" v-if="displayPicker" />
	</span>
</div>`,
    props: ['color'],
    data() {
        return {
            colors: {
                hex: '#000000',
            },
            colorValue: '',
            displayPicker: false,

        }
    },
    mounted() {
        this.setColor(this.color || '#000000');
    },
    methods: {
        setColor(color) {
            this.updateColors(color);
            this.colorValue = color;
        },
        updateColors(color) {
            if(color.slice(0, 1) == '#') {
                this.colors = {
                    hex: color
                };
            }
            else if(color.slice(0, 4) == 'rgba') {
                var rgba = color.replace(/^rgba?\(|\s+|\)$/g,'').split(','),
                    hex = '#' + ((1 << 24) + (parseInt(rgba[0]) << 16) + (parseInt(rgba[1]) << 8) + parseInt(rgba[2])).toString(16).slice(1);
                this.colors = {
                    hex: hex,
                    a: rgba[3],
                }
            }
        },
        showPicker() {
            document.addEventListener('click', this.documentClick);
            this.displayPicker = true;
        },
        hidePicker() {
            document.removeEventListener('click', this.documentClick);
            this.displayPicker = false;
        },
        togglePicker() {
            this.displayPicker ? this.hidePicker() : this.showPicker();
        },
        updateFromInput() {
            this.updateColors(this.colorValue);
        },
        updateFromPicker(color) {
            this.colors = color;
            if(color.rgba.a == 1) {
                this.colorValue = color.hex;
            }
            else {
                this.colorValue = 'rgba(' + color.rgba.r + ', ' + color.rgba.g + ', ' + color.rgba.b + ', ' + color.rgba.a + ')';
            }
        },
        documentClick(e) {
            var el = this.$refs.colorpicker,
                target = e.target;
            if(el !== target && !el.contains(target)) {
                this.hidePicker()
            }
        }
    },
    watch: {
        colorValue(val) {
            if(val) {
                this.updateColors(val);
                this.$emit('input', val);
                //document.body.style.background = val;
            }
        }
    },
});

let app = new Vue({
    el: '.settings',
    data: {
        date_format: siteSettings['date_format'],
        colorValue:'',
        defaultColor: siteSettings['review_text_color'],
        fontSizeDifference:siteSettings['review_fontsize'],
        reviewerNameFontSize: 20 + siteSettings['review_fontsize'],
        reviewFontSize: 15 + siteSettings['review_fontsize'],
        reviewDateFontSize: 10 + siteSettings['review_fontsize'],
        reviews: [],
        priceMarkup: siteSettings['price_markup'],
        comparePriceMarkup: siteSettings['compare_at_price_markup'],
        price_by_amount: siteSettings['price_by_amount'],
        price_by_percent: siteSettings['price_by_percent'],
        compare_at_price_by_amount: siteSettings['compare_at_price_by_amount'],
        compare_at_price_by_percent: siteSettings['compare_at_price_by_percent'],
    },
    created: function () {
       this.getReviews();

    },
    methods: {
        async getReviews() {
            let url = '/';
            let reviews = await fetch(url + 'review/get-user-reviews');
            reviews = await  reviews.json();
            this.reviews = reviews;

        },
        increaseFontSize() {
            this.fontSizeDifference++;
            this.reviewerNameFontSize++;
            this.reviewFontSize++;
            this.reviewDateFontSize++;
        },

        decreaseFontSize() {
            this.fontSizeDifference--;
            this.reviewerNameFontSize--;
            this.reviewFontSize--;
            this.reviewDateFontSize--;
        },
        formattedDate(formats, val) {
            return formats[val];
        }

    },


});

$(document).ready(function () {
   $('.settings').fadeIn();
   $('[data-toggle=tooltip]').tooltip();

    $('body').on('click', '.paidFeature', function(){
        $("#subscribeModal").modal('show');
    });

    $(document).on('click', '.field-usersetting-custom_pricing_rules', function () {
        if (!$(this).hasClass('paidFeature')) {
            if ($('.custom_pricing_rules_check').is(':checked')) {
                $('.pricingrulecontainerr').removeClass('d-none')
            } else {
                $('.pricingrulecontainerr').addClass('d-none')
            }
        }
    })

    $(document).on('click', '.field-usersetting-use_default_currency', function () {
        if (!$(this).hasClass('paidFeature')) {
            if ($('.default_currency_check').is(':checked')) {
                $('.defaultCurrencyRate').removeClass('d-none')
            } else {
                $('.defaultCurrencyRate').addClass('d-none')
            }
        }
    })

    $('.createProductPricingRule').click(function () {
        $('.productPricingRulesErrors').empty().addClass('d-none')
        $("#productPricingRuleModal").modal('show')
        $('.saveProductPricingRule').attr('data-action', 'create')
    })

    function getDataForProductPricingRuleRequest() {
        let tokenName = $("meta[name='csrf-param']").attr('content')
        return {
            'ProductPricingRule' : {
                'price_min': $('.productPricingRuleMinValue').val(),
                'price_max': $('.productPricingRuleMaxValue').val(),
                'price_markup': $('.productPricingRulePriceMarkupValue').val(),
                'compare_at_price_markup': $('.productPricingRuleCompareAtPriceMarkupValue').val(),
                'price_by_percent': $('.productPricingRulePriceByPercent').val() || 0,
                'price_by_amount': $('.productPricingRulePriceByAmount').val() || 0,
                'compare_at_price_by_amount': $('.productPricingRuleCompareAtPriceByAmount').val() || 0,
                'compare_at_price_by_percent': $('.productPricingRuleCompareAtPriceByPercent').val() || 0,
            },
            [tokenName]: $("meta[name='csrf-token']").attr('content')
        }
    }

    $('.valueNotNegative').change(function() {
        notNegativeValue($(this))
    })

    function notNegativeValue(e, changeTo = 0) {
        if (Number(e.val()) < 0) {
            e.val(changeTo)
        }
    }

    $(".saveProductPricingRule").click( async function () {
        $(this).addClass('d-none')
        $('.currencyLoading').removeClass('d-none')
        let action = $(this).attr('data-action')
        $('#productPricingRuleModal .text-danger').each(function() {
            $(this).remove()
        })
        let data = getDataForProductPricingRuleRequest();
        if (action === 'create') {
            $.ajax({
                type: "POST",
                url: '/product-pricing-rule/create',
                data: data,
                success: productPricingRulesSuccess,
            })
        } else {
            let id = $('.productPricingRuleId').val()
            $.ajax({
                type: "POST",
                url: '/product-pricing-rule/update?id=' + id,
                data: data,
                success: productPricingRulesSuccess,
            })
        }
    })

    function productPricingRulesSuccess(response) {
        $('.saveProductPricingRule').removeClass('d-none')
        $('.currencyLoading').addClass('d-none')
        let data = JSON.parse(response)
        if (data.success) {
            window.location.reload()
        } else {
            $('.productPricingRulesErrors').removeClass('d-none')
            if (typeof data.message === 'object') {
                Object.keys(data.message).forEach(key => {
                    $('div[data-field-name="'+ key +'"]').append(`<p class="text-danger"> ${data.message[key]} </p>`)
                })
            } else {
                $('.productPricingRulesErrors').append(`<p class="text-danger"> ${data.message} </p>`)
            }
        }
    }

    $(document).on('click', '.editProductPricingRuleButton', function() {
        $('.productPricingRulesErrors').empty().addClass('d-none')
        $('.productPricingRuleId').remove()
        $('#productPricingRuleModal').modal('show')
        let id = $(this).attr('data-id')
        let price_markup = $('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_price_markup').attr('data-value')
        let comp_price_markup = $('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_compare_at_price_markup').attr('data-value')
        if (price_markup == 1) {
            $(".productPricingRulePriceByPercentContainer").addClass('d-none');
            $(".productPricingRulePriceByAmountContainer").removeClass('d-none');
        } else {
            $(".productPricingRulePriceByPercentContainer").removeClass('d-none');
            $(".productPricingRulePriceByAmountContainer").addClass('d-none');
        }
        if (comp_price_markup == 1) {
            $(".productPricingRuleCompareAtPriceByPercentContainer").addClass('d-none');
            $(".productPricingRuleCompareAtPriceByAmountContainer").removeClass('d-none');
        } else {
            $(".productPricingRuleCompareAtPriceByPercentContainer").removeClass('d-none');
            $(".productPricingRuleCompareAtPriceByAmountContainer").addClass('d-none');
        }
        $('.productPricingRulePriceMarkupValue').val(price_markup)
        $('.productPricingRuleCompareAtPriceMarkupValue').val(comp_price_markup)
        $('.productPricingRuleMinValue').val($('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_min_price').attr('data-value'))
        $('.productPricingRuleMaxValue').val($('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_max_price').attr('data-value'))
        $('.productPricingRulePriceByPercent').val($('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_price_by_percent').attr('data-value') || 0)
        $('.productPricingRuleCompareAtPriceByPercent').val($('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_compare_at_price_by_percent').attr('data-value') || 0)
        $('.productPricingRulePriceByAmount').val($('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_price_by_amount').attr('data-value') || 0)
        $('.productPricingRuleCompareAtPriceByAmount').val($('.productPricingRuleItem[data-id="'+ id +'"] .product_pricing_rule_compare_at_price_by_amount').attr('data-value') || 0)
        $('.saveProductPricingRule').attr('data-action', 'update')
        $('#productPricingRuleModal .modal-dialog .modal-body .form-row').append(`<input type="hidden" value="${id}" class="productPricingRuleId">` )
    })

    $('.pricingRuleMarkupChanger').change(function() {
        let action = Number($(this).attr('data-action'))
        let value = Number($(this).val())
        let comAtPriceAmount = $('.productPricingRuleCompareAtPriceByAmountContainer')
        let comAtPricePercent = $('.productPricingRuleCompareAtPriceByPercentContainer')
        let pricePercent = $('.productPricingRulePriceByPercentContainer')
        let priceAmount = $('.productPricingRulePriceByAmountContainer')
        if (action === 1) {
            if (value === 0) {
                pricePercent.removeClass('d-none')
                priceAmount.addClass('d-none')
            } else if (value === 1) {
                priceAmount.removeClass('d-none')
                pricePercent.addClass('d-none')
            }
        } else if (action === 2) {
            if (value === 0) {
                comAtPricePercent.removeClass('d-none')
                comAtPriceAmount.addClass('d-none')
            } else if (value === 1) {
                comAtPriceAmount.removeClass('d-none')
                comAtPricePercent.addClass('d-none')
            }
        }
    })

});

