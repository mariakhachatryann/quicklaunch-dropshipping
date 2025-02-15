
var baseObject;
class Popup {
    constructor(){
        baseObject = this;
        this.extension_version = '1.4.5';
        this.data = this.initVueData();
        this.data.editData = editData;
        this.getDataUrl = getDataUrl;
        this.getReviewsUrl = getReviewsUrl;
        this.accessToken = token;
        this.domain = '/api/';
        this.importAppVueInstance(this.data, this);
        this.initComponents(this.data);
    }
    async setIndexData(data, baseObject) {
        const indexData = await (this.fetchRequest(`${baseObject.domain}profile/index`, 'GET', false));
        data.informationIndexRequest = indexData.settings;
        data.userProductPricingRules = indexData.productPricingRules;
        const settings = indexData.settings;
        data.importTypeArr.forVariants =  settings.change_variants_option_name;
        data.importTypeArr.SKU = settings.sku_import_type;
        data.importTypeArr.Price = settings.price_import_type;
        data.importTypeArr.Quantity = settings.stock_count_import_type;
        data.importTypeArr.CompareAtPrice = settings.price_import_type;
        data.importTypeArr.IMG = settings.image_import_type;
        data.importTypeArr.generateAiContent = settings.generate_ai_content;

        if (!settings.price_import_type) {
            data.priceReadonly = true
        }
        if (!settings.stock_count_import_type) {
            data.quantityReadonly = true
        }

        data.collectionsArray = indexData.collections;
        let availableFeatures = indexData.available_features;

        data.disabledImportType.forVariants = availableFeatures.change_variants_option_name;
        data.disabledImportType.SKU = availableFeatures.sku_import_type;
        data.disabledImportType.Price =  availableFeatures.price_import_type;
        data.disabledImportType.Quantity = availableFeatures.stock_count_import_type;
        data.disabledImportType.CompareAtPrice = availableFeatures.price_import_type;
        data.disabledImportType.IMG = availableFeatures.image_import_type;
        data.disabledImportType.generateAiContent = availableFeatures.generate_ai_content;
        data.disabledImportType.variant_price_markup = availableFeatures.variant_price_markup;
        data.disabledImportType.product_currency_convertor = availableFeatures.product_currency_convertor;
        data.disabledImportType.custom_pricing_rules = availableFeatures.custom_pricing_rules;

        if (settings.use_default_currency && availableFeatures.product_currency_convertor) {
            data.supplierCurrency = settings.default_currency_id
            data.shopifyCurrency = settings.currency_id
            data.currencyRate = settings.currency_rate
            data.currencyConvertorEnabled = true
        }

    }
    async setCurrenciesData(data, baseObject) {
        data.currencies = await (this.fetchRequest(`${baseObject.domain}currency/index`, 'GET', false))
    }
    initComponents(data){
        Vue.use(CKEditor);
        // Vue.use(DatePicker);
        Vue.component('star-rating', VueStarRating.default);
        Vue.use(PrettyCheckbox);
        Vue.use(Lightbox);
        Vue.use(moment);
        data.editor = ClassicEditor;
        $('[data-toggle="hover"]').popover({ trigger: "hover" })
        $(document).keydown(function(e) {
            if (e.keyCode === 27) {
                data.displayLightBoxDiv = 'none';
                data.lightBoxImgSrc = '';
            }
        });
    }
    initVueData() {
        return {
            displayEditAllSecCheckbox: false,
            editAllSecCheckbox: {
                title: true,
                sizeTable: true,
                price: true,
                description: true,
                collectionsArray: true,
                images: true,
                priceByPercent: true,
                priceByAmount: true,
                allOptions: true,
                compareAtPrice: true,
                brand: true,
                weight:true,
                compareAtPriceByPercent: true,
                compareAtPriceByAmount: true,
            },
            defaultEditCompareAtPriceByAmount: 0,
            defaultEditPriceByAmount: 0,
            defaultEditPriceByPercent: 0,
            defaultEditCompareAtPriceByPercent: 0,
            // use this version
            scrapData: null,
            scrapUrl: '',
            addData:{},
            editor: '',
            tdDisabledArr: [],
            variantsPriceMarkups: [],
            variant_price_markup: '0',
            variant_compare_at_price_markup: '0',
            variant_price_by_percent: 0,
            variant_price_by_amount: 0,
            variant_final_price: 0,
            variant_price: 0,
            variant_compare_at_price: 0,
            variant_compare_at_price_by_amount: 0,
            variant_compare_at_price_by_percent: 0,
            variant_get_from_product_markup: true,
            variant_index: true,
            images: [],
            allOptions: [],
            oldAllOptions: [],
            reviews: [],
            addImageSrc: '',
            checkedReviews: [],
            chooseReviews: [],
            title: '',
            generateTitle: false,
            generatedTitle: null,
            generatingTitle: false,
            price: 0,
            imported_from: null,
            defaultPrice:'',
            priceReadonly:false,
            quantityReadonly:false,
            isVariantPriceNegative: false,
            stockCount: '',
            vendor:'',
            productType:'',
            checkedImages: [],
            loadingImgUrl: '',
            description: '',
            generateDescription: false,
            generatedDescription: null,
            generatingDescription: false,
            compareAtPriceByPercent: 0,
            compareAtPriceByAmount: 0,
            priceByPercent: 0,
            priceByAmount: 0,
            priceSelected: '0',
            compareAtPriceSelected: '0',
            allCheckedOrUncheckedReviews: 1,
            chooseVariationsItems: [],
            checkedVariationsItems: [],
            checkedVariationsName: {},
            allCheckedOrUncheckedVariations: 1,
            allImagesMyModal: [],
            preferVariationImageSrc: {},
            onlyVariationsImage: [],
            onlyOptionName: [],
            optionsWithValue: [],
            radioSelectAllOptions: 1,
            radioSelectReviews: 1,
            loading: false,
            storage: '',
            collectionsArray: [],
            token: "",
            selectedCollection: '',
            defaultCompareAtPriceByAmount: null,
            defaultPriceByAmount: null,
            defaultPriceByPercent: null,
            defaultCompareAtPriceByPercent: null,
            displayMassageNotValidImage: 'none',
            finalSKU: '',
            informationIndexRequest:{},
            brand: '',
            weightUnit:{'ounce':'oz','Ounce':'oz','pounds':'lb','Pounds':'lb','oz':'oz','lbs':'lb','kg':'kg','g':'g'},
            weightUnitForHtml:{'ounce':'oz','pounds':'lb','kg':'kg','g':'g'},
            weight: '',
            selectedWeightUnit:'',
            compareAtPrice: '',
            productUrl: '',
            name: [],
            noImage: '',
            logoImage:'',
            nameQueue: [],
            importTypeArr: {},
            disabledImportType: {},
            subscribeLink: '/profile/subscribe',
            numberMaxVariant:101,
            lightBoxImgSrc:'',
            displayLightBoxDiv:'none',
            displayVariantImageLoad:'block',
            pageImageLoad:'block',
            productLimitsMessage:'',
            whichSiteScrape:'',
            thereIsVariants:false,
            pageInfoModalBody:'',
            fullOptionsName:[],
            thereIsImage:false,
            focusVariantInputValue:'',
            repeatValueIndexList:[],
            currencies: [],
            supplierCurrency: null,
            shopifyCurrency: null,
            currencyRate: 1,
            defaultCurrencyRate: 1,
            minPriceMarkup: null,
            maxPriceMarkup: null,
            reservePrice: 0,
            editDataListChooseId:'',
            pageLoading: false,
            userProductPricingRules: [],
            defaultQuantity: '',
            loadingMessage: '',
            helpMessageKey: '',
            currencyConvertorEnabled: false,
            editLoading: {
                title: false,
                description: false,
                price: false,
                priceByPercent: false,
                compareAtPriceByPercent: false,
                brand: false,
                weight: false,
                allOptions: false,
                images: false,
            },
            // Messages
            tooltipObject:{
                IMG:'Upload images for each variant.',
                SKU:' Use various options for each product.',
                Price:'Edit different prices for each product.',
                CompareAtPrice:'Edit prices for each product.',
                Quantity:'Use different inventory quantity for each variant',
                forVariants:'Edit variant options names',
                Color: 'Edit different color options for each product.',
                Size: 'Edit different size options for each product.'
            },
            loadingMessageList: {
                getProductData: 'Product data is loading, please wait!',
                importProduct: 'Import process is running, please wait!'
            },
            invalidUrlMessage: 'Please insert valid product url',
            showCreateCollectionInput: false,
            newCollectionValue: '',
        }
    }
    async importAppVueInstance(data,baseObject){
        this.vueInstance = new Vue({
            el: '#import-app',
            data,
            async mounted() {
                if (this.scrapUrl) {
                  this.getAddData();
                }

            },
            computed: {
                finalPrice: {
                    get() {
                        if (!(this.price == '0') && !(this.price == '0.00')) {
                            if (this.priceSelected === '0') {
                                return (Number(this.price) + (this.priceByPercent / 100 * this.price )).toFixed(2);
                            } else {
                                let finalP = Number(this.price) + Number(this.priceByAmount);
                                finalP = Number(finalP);

                                return finalP.toFixed(2);
                            }
                        }
                        let finalP = 0;
                        return finalP.toFixed(2);
                    },
                    set(name) {
                        if (!(this.price == '0') && !(this.price == '0.00')) {
                            if (this.priceSelected === '0') {
                                let takeTwoFixed = (name - this.price) * 100 / this.price;
                                this.priceByPercent = takeTwoFixed.toFixed(2);
                            } else {

                                let takeTwoFixed = name - Number(this.price);
                                this.priceByAmount = takeTwoFixed.toFixed(2);
                            }

                        }
                    }

                },
                finalCompareAtPrice: {
                    get() {
                        if (this.compareAtPriceSelected === '0') {
                            let finalCompareAtPrice = Number(this.compareAtPrice) + (this.compareAtPriceByPercent / 100 * this.compareAtPrice);
                            return finalCompareAtPrice.toFixed(2);
                        } else {
                            let finalCompareAtPrice = Number(this.compareAtPrice) + Number(this.compareAtPriceByAmount);
                            return finalCompareAtPrice.toFixed(2)
                        }
                    },
                    set(name) {
                        if (this.compareAtPriceSelected === '0') {
                            let takeTwoFixed = (name - this.compareAtPrice) * 100 / this.compareAtPrice ;
                            this.compareAtPriceByPercent = takeTwoFixed.toFixed(2);
                        } else {
                            let takeTwoFixed = name - Number(this.compareAtPrice);
                            this.compareAtPriceByAmount = takeTwoFixed.toFixed(2);
                        }

                    }

                },
                displayVariationList() {
                    if (this.radioSelectAllOptions) {
                        if(this.allOptions?.length){
                            if (this.allCheckedOrUncheckedVariations) {
                                this.makeAllOptionsItemChecked();
                            } else {
                                this.chooseVariationsItems = [0]
                            }
                        }
                        return 'block';
                    } else {
                        return 'none';
                    }
                },

                displayReviewList() {
                    if (this.radioSelectReviews) {
                        return 'block';
                    } else {
                        return 'none';
                    }
                },

                checkByAmount() {
                    if (this.displayEditAllSecCheckbox) {
                        this.priceByAmount = this.defaultEditPriceByAmount;
                        this.priceByPercent = this.defaultEditPriceByPercent;
                    }else{
                        this.priceByAmount = this.defaultPriceByAmount;
                        this.priceByPercent = this.defaultPriceByPercent;
                    }
                    return this.priceSelected === '1' ? 'block' : 'none'
                },
                checkCompareAtPriceByAmount() {
                    if (this.displayEditAllSecCheckbox) {
                        this.compareAtPriceByPercent = this.defaultEditCompareAtPriceByPercent;
                        this.compareAtPriceByAmount = this.defaultEditCompareAtPriceByAmount;
                    }else{
                        this.compareAtPriceByPercent = this.defaultCompareAtPriceByPercent;
                        this.compareAtPriceByAmount = this.defaultCompareAtPriceByAmount;
                    }
                    return this.compareAtPriceSelected === '1' ? 'block' : 'none'
                },
                checkperCent() {
                    if (this.displayEditAllSecCheckbox) {
                        this.priceByAmount = this.defaultEditPriceByAmount;
                        this.priceByPercent = this.defaultEditPriceByPercent;
                    }else{
                        this.priceByAmount = this.defaultPriceByAmount;
                        this.priceByPercent = this.defaultPriceByPercent;
                    }
                    return this.priceSelected === '0' ? 'block' : 'none'


                },
                checkCompareAtPricePerCent() {
                    if (this.displayEditAllSecCheckbox) {
                        this.compareAtPriceByPercent = this.defaultEditCompareAtPriceByPercent;
                        this.compareAtPriceByAmount = this.defaultEditCompareAtPriceByAmount;
                    }else{
                        this.compareAtPriceByPercent = this.defaultCompareAtPriceByPercent;
                        this.compareAtPriceByAmount = this.defaultCompareAtPriceByAmount;
                    }
                    return this.compareAtPriceSelected === '0' ? 'block' : 'none'
                },
                displayMaxVariantMessage(){
                    if(this.allOptions?.length <= this.numberMaxVariant){
                        return false
                    }
                    return true
                },
                showData :{
                    get() {
                        return Object.keys(this.addData)?.length || (this.editData && Object.keys(this.editData)?.length) ? 'block':'none'
                    },
                    set(value) {
                        return value
                    }
                },
                quantityStatus(){
                    const quantityIndex = this.onlyOptionName.indexOf('Quantity');
                    return this.tdDisabledArr.includes(quantityIndex);
                }
            },
            watch: {
                tdDisabledArr(value, oldValue) {
                    const priceIndex = this.onlyOptionName.indexOf("Price")
                    const quantityIndex = this.onlyOptionName.indexOf("Quantity")
                    if ((oldValue.indexOf(priceIndex) !== -1 && value.indexOf(priceIndex) === -1)
                        || (oldValue.indexOf(priceIndex) === -1 && value.indexOf(priceIndex) !== -1)
                        || (oldValue.indexOf(quantityIndex) !== -1 && value.indexOf(quantityIndex) === -1)
                        || (oldValue.indexOf(quantityIndex) === -1 && value.indexOf(quantityIndex) !== -1)) {
                        if (this.allOptions?.length && this.priceSelected === '1') {
                            this.calculatePrice(this.priceByAmount, 1)
                        } else {
                            this.calculatePrice(this.priceByPercent)
                        }
                    }
                },
                currencyRate(value) {
                    if (value < 0) {
                        this.currencyRate = 1
                        value = 1
                    }
                    this.price = (Number(this.reservePrice) * Number(value)).toFixed(2)
                    if (this.allOptions?.length && this.priceSelected === '1') {
                        this.calculatePrice(this.priceByAmount, 1)
                    } else {
                        this.calculatePrice(this.priceByPercent)
                    }
                    const priceIndex = this.onlyOptionName.indexOf("Price")
                    this.variantsPriceMarkups = this.variantsPriceMarkups.map(i => {
                        i.price = Number(this.oldAllOptions[i.id][priceIndex].name) * Number(value)
                        if (i.price_markup == 1) {
                            i.variant_final_price = Number(i.price + Number(i.price_by_amount)).toFixed(2)
                        } else {
                            i.variant_final_price = Number(i.price + ((i.price / 100) * i.price_by_percent)).toFixed(2)
                        }
                        if (i.compare_at_price_markup == 1) {
                            i.variant_compare_at_price = Number(Number(i.variant_final_price) + Number(i.compare_at_price_by_amount)).toFixed(2)
                        } else {
                            i.variant_compare_at_price = Number(Number(i.variant_final_price) + ((i.variant_final_price / 100) * i.compare_at_price_by_percent )).toFixed(2)
                        }
                        return i
                    })
                },
                generateTitle(isChecked) {
                    this.generateAiContent(isChecked, 'title', 'generatedTitle', 'generatingTitle');
                },

                generateDescription(isChecked) {
                    this.generateAiContent(isChecked, 'description', 'generatedDescription', 'generatingDescription');
                },

                variant_get_from_product_markup(val) {
                    if (val) {
                        const priceIndex = this.onlyOptionName.indexOf("Price")
                        const comparePriceIndex = this.onlyOptionName.indexOf('CompareAtPrice')
                        this.variant_price = Number(this.oldAllOptions[this.variant_index][priceIndex].name * this.currencyRate)
                        this.variant_final_price = Number(this.allOptions[this.variant_index][priceIndex].name)
                        this.variant_compare_at_price = Number(this.allOptions[this.variant_index][comparePriceIndex].name)
                        this.variant_price_markup = this.priceSelected
                        this.variant_compare_at_price_markup = this.compareAtPriceSelected
                        this.variant_price_by_percent = this.priceByPercent
                        this.variant_price_by_amount = this.priceByAmount
                        this.variant_compare_at_price_by_amount = this.compareAtPriceByAmount
                        this.variant_compare_at_price_by_percent = this.compareAtPriceByPercent
                        this.allOptions[this.variant_index][priceIndex].changed = false
                        this.allOptions[this.variant_index][priceIndex].price_markup = this.priceSelected
                        this.allOptions[this.variant_index][priceIndex].compare_at_price_markup = this.compareAtPriceSelected
                        this.allOptions[this.variant_index][priceIndex].price_by_percent = this.priceByPercent
                        this.allOptions[this.variant_index][priceIndex].price_by_amount = this.priceByAmount
                        this.allOptions[this.variant_index][priceIndex].compare_at_price_by_amount = this.compareAtPriceByAmount
                        this.allOptions[this.variant_index][priceIndex].compare_at_price_by_percent = this.compareAtPriceByPercent
                        this.allOptions[this.variant_index][comparePriceIndex].name = Number(this.allOptions[this.variant_index][comparePriceIndex].name)
                        this.allOptions[this.variant_index][priceIndex].name = Number(this.oldAllOptions[this.variant_index][priceIndex].name)
                        if (this.priceSelected == 1) {
                            this.calculatePrice(this.priceByAmount, 1)
                        } else {
                            this.calculatePrice(this.priceByPercent)
                        }
                        this.variantsPriceMarkups = this.variantsPriceMarkups.filter((markup) => markup.id !== this.variant_index)
                    }
                },
                variant_price_by_percent(val) {
                    if (val < 0) {
                        this.variant_price_by_percent = 0
                        val = 0
                    }
                    if(val !== this.priceByPercent) {
                        this.variant_get_from_product_markup = false
                    }
                    this.variant_final_price = (((Number(this.variant_price) / 100) * Number(val)) + Number(this.variant_price)).toFixed(2)

                    if(this.variant_compare_at_price_markup == '0') {
                        this.variant_compare_at_price = (((Number(this.variant_final_price) / 100) * Number(this.variant_compare_at_price_by_percent)) + Number(this.variant_final_price)).toFixed(2)
                    } else {
                        this.variant_compare_at_price = (Number(this.variant_final_price) + Number(this.variant_compare_at_price_by_amount)).toFixed(2)
                    }
                },
                variant_price_by_amount(val) {
                    if (val < 0) {
                        this.variant_price_by_amount = 0
                        val = 0
                    }
                    if(val !== this.priceByAmount) {
                        this.variant_get_from_product_markup = false
                    }
                    this.variant_final_price = (Number(this.variant_price) + Number(val)).toFixed(2)
                    if(this.variant_compare_at_price_markup == '0') {
                        this.variant_compare_at_price = (((Number(this.variant_final_price) / 100) * Number(this.variant_compare_at_price_by_percent)) + Number(this.variant_final_price)).toFixed(2)
                    } else {
                        this.variant_compare_at_price = (Number(this.variant_final_price) + Number(this.variant_compare_at_price_by_amount)).toFixed(2)
                    }
                },
                variant_compare_at_price_by_percent(val) {
                    if (val < 0) {
                        this.variant_compare_at_price_by_percent = 0
                        val = 0
                    }
                    if(val !== this.compareAtPriceByPercent) {
                        this.variant_get_from_product_markup = false
                    }

                    this.variant_compare_at_price = (((Number(this.variant_final_price) / 100) * Number(val)) + Number(this.variant_final_price)).toFixed(2)
                },
                variant_compare_at_price_by_amount(val) {
                    if (val < 0) {
                        this.variant_compare_at_price_by_amount = 0
                        val = 0
                    }
                    if(val !== this.compareAtPriceByAmount) {
                        this.variant_get_from_product_markup = false
                    }
                    this.variant_compare_at_price = (Number(this.variant_final_price) + Number(val)).toFixed(2)
                },
                variant_price_markup(val) {
                    if(val !== this.priceSelected) {
                        this.variant_get_from_product_markup = false
                    }
                    if (val === '0') {
                        this.variant_final_price = (((this.variant_price / 100) * Number(this.variant_price_by_percent)) + Number(this.variant_price))
                        this.variant_final_price = this.variant_final_price.toFixed(2)
                        if (this.variant_compare_at_price_markup === '0') {
                            this.variant_compare_at_price = (((Number(this.variant_final_price) / 100) * Number(this.variant_compare_at_price_by_percent)) + Number(this.variant_final_price)).toFixed(2)
                        } else {
                            this.variant_compare_at_price = (Number(this.variant_final_price) + Number(this.variant_compare_at_price_by_amount)).toFixed(2)
                        }

                    } else {
                        this.variant_final_price = (Number(this.variant_price) + Number(this.variant_price_by_amount)).toFixed(2)
                        if (this.variant_compare_at_price_markup === '0') {
                            this.variant_compare_at_price = (((Number(this.variant_final_price) / 100) * Number(this.variant_compare_at_price_by_percent)) + Number(this.variant_final_price)).toFixed(2)
                        } else {
                            this.variant_compare_at_price = (Number(this.variant_final_price) + Number(this.variant_compare_at_price_by_amount)).toFixed(2)
                        }
                    }
                },
                variant_compare_at_price_markup(val) {
                    if(val !== this.compareAtPriceSelected) {
                        this.variant_get_from_product_markup = false
                    }
                    if (val === '0') {
                        this.variant_compare_at_price = (((Number(this.variant_final_price) / 100) * Number(this.variant_compare_at_price_by_percent)) + Number(this.variant_final_price))
                        this.variant_compare_at_price = this.variant_compare_at_price.toFixed(2)
                    } else {
                        this.variant_compare_at_price = (Number(this.variant_final_price) + Number(this.variant_compare_at_price_by_amount)).toFixed(2)
                    }
                },
                priceSelected(val) {
                    if (val === '1') {
                        this.calculatePrice(this.priceByAmount, 1)
                    } else {
                        this.calculatePrice(this.priceByPercent)
                    }
                },
                priceByAmount(val) {
                    if (val < 0) {
                        this.priceByAmount = 0
                        val = 0
                    }
                    if (this.allOptions?.length && this.priceSelected === '1') {
                        this.calculatePrice(val, 1)
                    }
                },
                priceByPercent(val) {
                    if (val < 0) {
                        this.priceByPercent = 0
                        val = 0
                    }
                    if (this.allOptions?.length && this.priceSelected === '0') {
                        this.calculatePrice(val)
                    }
                },

                compareAtPriceSelected(val) {
                    if (val === '1') {
                        this.calculateCompareAtPrice(this.compareAtPriceByAmount, 1)
                    } else {
                        this.calculateCompareAtPrice(this.compareAtPriceByPercent)
                    }
                },
                compareAtPriceByAmount(val) {
                    if (val < 0) {
                        this.compareAtPriceByAmount = 0
                        val = 0
                    }
                    if (this.allOptions?.length && this.compareAtPriceSelected === '1') {
                        this.calculateCompareAtPrice(val, 1)
                    }
                },
                compareAtPriceByPercent(val) {
                    if (val < 0) {
                        this.compareAtPriceByPercent = 0
                        val = 0
                    }
                    if (this.allOptions?.length && this.compareAtPriceSelected === '0') {
                        this.calculateCompareAtPrice(val)
                    }
                },
                allCheckedOrUncheckedReviews(val) {
                    if (val) {
                        let chooseReview = [];
                        for (let reviewItem in this.reviews) {
                            chooseReview.push(Number(reviewItem));
                        }
                        this.chooseReviews = chooseReview;
                    } else {
                        this.chooseReviews = [];
                    }
                },
                allCheckedOrUncheckedVariations(val) {
                    if (val) {
                        const chooseVariationsItem = [];
                        const maxLength = this.allOptions?.length<this.numberMaxVariant ? this.allOptions?.length : this.numberMaxVariant;
                        for (let optionIndex = 0; optionIndex < maxLength; optionIndex++) {
                            chooseVariationsItem.push(optionIndex);
                        }
                        this.chooseVariationsItems = chooseVariationsItem;
                    } else {
                        this.chooseVariationsItems = [0];
                    }
                }
            },
            methods: {
                enableCurrencyConvertor(e) {
                    if (!this.disabledImportType.product_currency_convertor) {
                        this.showSubscribeLinkModal(true,false)
                        e.target.checked = false
                    } else {
                        this.currencyConvertorEnabled = !this.currencyConvertorEnabled
                        if (this.currencyConvertorEnabled) {
                            this.currencyRate = this.defaultCurrencyRate
                        } else {
                            this.defaultCurrencyRate = this.currencyRate
                            this.currencyRate = 1
                        }
                    }
                },
               async changeCurrency() {
                    if (this.supplierCurrency && this.shopifyCurrency) {
                        const result = await baseObject.fetchRequest(
                            `${baseObject.domain}currency/convert`,
                            'POST',
                            {
                                from: this.supplierCurrency,
                                to: this.shopifyCurrency
                            }
                        );
                        let fixed = 2
                        if (result < 0.1) {
                            fixed = 3
                        }
                        if (result < 0.01) {
                            fixed = 4
                        }
                        this.currencyRate = result.toFixed(fixed);
                    }
                },
                async generateAiContent(isChecked, field, generatedField, generatingField) {
                    if (isChecked) {
                        if (!this[generatedField]) {
                            this[generatingField] = true;
                            this[field] = 'Generating AI ' + field + ' please wait...';
                            let result =  await baseObject.fetchRequest(
                              `${baseObject.domain}product/generate-content`,
                              'POST',
                              {
                                  url: this.productUrl,
                                  type: field
                              }
                            );
                            this[generatingField] = false;
                            this[generatedField] = result.content ?? null;
                        }
                        this[field] = this[generatedField];
                    } else {
                        this[field] = this.addData[field];
                    }
                },

                changeVariantFinalPrice(e) {
                    if(this.variant_price_markup === '0') {
                        this.variant_price_by_percent = ((Number(e.target.value) - this.variant_price) /  (this.variant_price / 100)).toFixed(2)
                    } else {
                        this.variant_price_by_amount = (Number(e.target.value) - this.variant_price).toFixed(2)
                    }

                },
                changeVariantCompareAtPrice(e) {
                    this.variant_get_from_product_markup = false
                    if (this.variant_compare_at_price_markup === '0') {
                        this.variant_compare_at_price_by_percent = ((Number(e.target.value)  - this.variant_final_price ) / (this.variant_final_price  / 100)).toFixed(2)
                    } else {
                        this.variant_compare_at_price_by_amount = (Number(e.target.value) - this.variant_final_price).toFixed(2)
                    }
                },
                variantMarkupSave() {
                    const finalPriceIndex = this.onlyOptionName.indexOf("Price")
                    const comparePriceIndex = this.onlyOptionName.indexOf('CompareAtPrice')
                    if (!this.variant_get_from_product_markup) {
                        let variantIndex = undefined
                        const variantData = this.variantsPriceMarkups.find((markup, index) => {
                            if (markup.id === this.variant_index) {
                                variantIndex = index
                                return markup
                            }
                        })
                        if(variantData) {
                            this.variantsPriceMarkups[variantIndex].variant_final_price = this.variant_final_price
                            this.variantsPriceMarkups[variantIndex].variant_compare_at_price = this.variant_compare_at_price
                            this.variantsPriceMarkups[variantIndex].price_markup = this.variant_price_markup
                            this.variantsPriceMarkups[variantIndex].compare_at_price_markup = this.variant_compare_at_price_markup
                            this.variantsPriceMarkups[variantIndex].price_by_percent = this.variant_price_by_percent
                            this.variantsPriceMarkups[variantIndex].price_by_amount = this.variant_price_by_amount
                            this.variantsPriceMarkups[variantIndex].compare_at_price_by_amount = this.variant_compare_at_price_by_amount
                            this.variantsPriceMarkups[variantIndex].compare_at_price_by_percent = this.variant_compare_at_price_by_percent
                            this.variantsPriceMarkups[variantIndex].get_from_product_markup = this.variant_get_from_product_markup
                            this.variantsPriceMarkups[variantIndex].price = this.variant_price
                        } else {
                            this.variantsPriceMarkups.push({
                                id: this.variant_index,
                                variant_final_price: this.variant_final_price,
                                price: this.variant_price,
                                variant_compare_at_price: this.variant_compare_at_price,
                                price_markup: this.variant_price_markup,
                                compare_at_price_markup: this.variant_compare_at_price_markup,
                                price_by_percent: this.variant_price_by_percent,
                                price_by_amount: this.variant_price_by_amount,
                                compare_at_price_by_amount: this.variant_compare_at_price_by_amount,
                                compare_at_price_by_percent: this.variant_compare_at_price_by_percent,
                                get_from_product_markup: this.variant_get_from_product_markup
                            })
                        }
                        this.allOptions[this.variant_index][finalPriceIndex].changed = true
                        this.allOptions[this.variant_index][finalPriceIndex].price_markup = this.variant_price_markup ?? 0
                        this.allOptions[this.variant_index][finalPriceIndex].compare_at_price_markup = this.variant_compare_at_price_markup ?? 0
                        this.allOptions[this.variant_index][finalPriceIndex].price_by_percent = this.variant_price_by_percent ?? 0
                        this.allOptions[this.variant_index][finalPriceIndex].price_by_amount = this.variant_price_by_amount ?? 0
                        this.allOptions[this.variant_index][finalPriceIndex].compare_at_price_by_amount = this.variant_compare_at_price_by_amount ?? 0
                        this.allOptions[this.variant_index][finalPriceIndex].compare_at_price_by_percent = this.variant_compare_at_price_by_percent ?? 0
                        this.allOptions[this.variant_index][comparePriceIndex].name = this.variant_compare_at_price ?? 0
                        this.allOptions[this.variant_index][finalPriceIndex].name = this.variant_final_price ?? 0
                    }

                },
                formatDate(d) {
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return `${d.getDate().toString().padStart(2, 0)} ${months[d.getMonth()]} ${d.getFullYear()}`
                },
                makeAllOptionsTdDisabled(){
                    this.tdDisabledArr = [];
                    for (let optionNameIndex = 0; optionNameIndex < this.onlyOptionName?.length; optionNameIndex++) {
                        let thereIsProperty = this.onlyOptionName[optionNameIndex] in this.importTypeArr;
                        if(thereIsProperty && this.importTypeArr[this.onlyOptionName[optionNameIndex]] === 1){
                            this.tdDisabledArr.push(optionNameIndex)
                        }else if(!thereIsProperty && this.importTypeArr['forVariants'] === 1){
                            this.tdDisabledArr.push(optionNameIndex)
                        }
                    }
                },
                makeAllOptionsItemChecked(){
                    let chooseVariationsItem = [];
                    let maxLength = this.allOptions?.length<this.numberMaxVariant ? this.allOptions?.length : this.numberMaxVariant;
                    for (let optionIndex = 0; optionIndex < maxLength; optionIndex++) {
                        chooseVariationsItem.push(optionIndex);
                    }
                    this.chooseVariationsItems = chooseVariationsItem;
                },
                makeReviewsChecked(){
                    const chooseReview = [];
                    for (let reviewItem in this.reviews) {
                        chooseReview.push(Number(reviewItem));
                    }
                    this.chooseEditReviews = [];
                    this.chooseReviews = chooseReview;
                    this.allCheckedOrUncheckedReviews = 1;
                },
                conditionAllOptions(name, property) {
                    if (this.editAllSecCheckbox[property]) {
                        this.editAllOptionsParse();
                    } else{
                        const { scrapData: { variants } } = this;
                        this.oldAllOptions = Array.from(JSON.parse(JSON.stringify(variants)));
                        this.allOptions = Array.from(JSON.parse(JSON.stringify(variants)));
                    }
                    this.makeAllOptionsTdDisabled();
                    this.makeAllOptionsItemChecked();
                    this.allCheckedOrUncheckedVariations = 1
                },
                conditionDes(property) {
                    if (this.editAllSecCheckbox[property]){
                        this.description = this.editData.body_html;
                    } else{
                        this.setDescription({body_html: this.scrapData.body_html});
                    }
                },
                conditionImages(property){
                    this.images=[];
                    this.checkedImages = [];
                    const { editData, scrapData } = this;
                    if (this.editAllSecCheckbox[property]){
                        this.checkedImages = editData.images;
                        this.images = editData.images;
                    } else{
                        this.checkedImages = scrapData.images;
                        this.images = scrapData.images;
                    }
                },
                conditionWeight(fieldName){
                    const { editData, scrapData } = this;
                    this[fieldName] = this.editAllSecCheckbox[fieldName] ? editData[fieldName] : scrapData[fieldName];
                    if(this.weight){
                        this.selectedWeightUnit = this.editAllSecCheckbox[fieldName] ? editData['weight_unit'] : scrapData['weight_unit'];
                    }else{
                        this.selectedWeightUnit = '';
                    }
                },
                changedSelectedEditField({fieldName}){

                    const name = `${fieldName.charAt(0).toUpperCase()}${fieldName.slice(1, fieldName.length)}`;
                    if(fieldName === 'allOptions'){
                        this.conditionAllOptions(name, fieldName);
                    }  else if (fieldName === 'images') {
                        this.conditionImages(fieldName);
                    } else if (fieldName === 'description') {
                        this.conditionDes(fieldName)
                    } else if(fieldName === 'weight') {
                        this.conditionWeight(fieldName)
                    }else {
                        const { editData, scrapData } = this;
                       this[fieldName] = this.editAllSecCheckbox[fieldName] ? editData[fieldName] : scrapData[fieldName]
                    }
                },
                async editScrap(fieldName) {
                    const { editData, scrapData } = this;
                    let scrapUrl = editData.productUrl;
                    const getDataUrl = baseObject.getDataUrl.trim();
                    if (scrapUrl && !scrapData){
                        this.editLoading[fieldName] = true;
                        try {
                            scrapUrl = encodeURIComponent(scrapUrl);
                            this.scrapData = await this.scrap(`${getDataUrl}?url=${scrapUrl}`);
                            this.editLoading[fieldName] = false;
                            this.scrapUrl = '';
                            this.changedSelectedEditField({
                                fieldName,
                            })
                        }catch (e) {
                            this.editLoading[fieldName] = false;
                            this.scrapUrl = '';
                            generateError(e.message, 505);
                        }
                    }else {
                        this.changedSelectedEditField({
                            fieldName
                        })
                    }

                },
                editAllOptionsParse(){
                    let editOptions =  this.parsVariationItems();
                    this.cloneVariants(editOptions);
                    this.calculateVariantsPrice();
                },
                edit() {
                    this.defaultIndexPriceCompareAtPrice();
                    this.defaultEditPriceCompareAtPrice();
                    this.radioSelectAllOptions = 1;
                    this.radioSelectReviews = 1;
                    this.description = this.editData.body_html;
                    this.brand = this.editData.brand;
                    this.weight = this.editData.weight;
                    this.selectedWeightUnit = this.editData.weight_unit;
                    this.selectedCollection = this.editData.collection;
                    this.title = this.editData.title;
                    this.images = this.editData.images;
                    this.price = this.editData.price;
                    this.compareAtPrice = this.editData.compare_at_price;
                    this.finalSKU = this.editData.productId;
                    this.reviews = this.editData.reviews[0] ? this.editData.reviews : [];
                    this.defaultQuantity = this.editData.stockCount;
                    this.stockCount = this.editData.stockCount;
                    this.options = this.editData.options;
                    this.thereIsVariants = this.editData.variants.length || false;
                    this.productUrl = this.editData.productUrl;
                    this.defaultEditAllSecCheckbox('edit');
                    this.attributionCompareAtPrice('editData');
                    this.checkedImageEditPage();
                    this.attributionPrice('editData');
                    this.editVariantsAction();
                    setTimeout(()=>{
                        $('[data-toggle="hover"]').popover({ trigger: "hover" })
                    },3000);
                    this.makeReviewsChecked();
                },
                defaultEditAllSecCheckbox(key) {
                    if(key === 'edit'){
                        for (let property in this.editAllSecCheckbox) {
                            this.editAllSecCheckbox[property] = true
                        }
                    }else {
                        for (let property in this.editAllSecCheckbox) {
                            this.editAllSecCheckbox[property] = false
                        }
                    }
                },
                allOptionsTh() {
                    let index = 0;
                    let allOptionsTh = Object.keys(this.editData.variants[0]);

                    let editOptionsWithValue = this.editData.options;
                    let optionName = [];
                    let editNameQueue = [];
                    let thArr = [];
                    for (let indexTh = 0; indexTh < allOptionsTh?.length; indexTh++) {
                        if (allOptionsTh[indexTh].includes(`option`)) {
                            let lastNumber = allOptionsTh[indexTh][allOptionsTh[indexTh].length-1];
                            optionName.push(editOptionsWithValue[lastNumber-1]['name']);
                            editNameQueue.push(editOptionsWithValue[lastNumber-1]['name']);
                            thArr.push({
                                input: false,
                                name: editOptionsWithValue[lastNumber-1]['name'],
                                type: "text"
                            });
                            index++
                        } else if (allOptionsTh[indexTh] === 'img') {
                            optionName.unshift('IMG');
                            thArr.unshift({
                                input: false,
                                name: "IMG",
                                type: "text"
                            })
                        }

                    }
                    thArr.push(
                        {input: false, name: "SKU",type: "text"},
                        {input: false, name: "Price", type: "text"},
                        {input: false, name: "CompareAtPrice", type: "text"},
                        {input: false, name: "Quantity", type: "text"}
                    );
                    optionName.push('SKU','Price','CompareAtPrice','Quantity');
                    this.onlyOptionName = optionName;
                    this.nameQueue = editNameQueue;
                    return thArr
                },
                parsVariationItems() {
                    let editAllOptions = this.editData.variants;
                    let editAllOptionsFirstItem = this.editData.variants[0];
                    delete editAllOptionsFirstItem.inventory_policy;
                    delete editAllOptionsFirstItem.inventory_management;
                    let variantPropertyKey = Object.keys(editAllOptionsFirstItem);
                    const findIndexOfDefaultSku = variantPropertyKey.findIndex((key) => key === 'default_sku')
                    variantPropertyKey.splice(findIndexOfDefaultSku,1);
                    let parsAllOptions = [];
                    parsAllOptions.push(this.allOptionsTh());
                    for (let item = 0; item < editAllOptions?.length; item++) {
                        delete editAllOptions[item].inventory_policy;
                        delete editAllOptions[item].inventory_management;
                        const default_sku = editAllOptions[item].default_sku;
                        let parsAllOptionItem = [];
                        for (let index = 0; index < variantPropertyKey.length; index++) {
                            let variantValue = editAllOptions[item][variantPropertyKey[index]];
                            if (variantPropertyKey[index] === 'img') {
                                if (variantValue === null) {
                                    parsAllOptionItem.unshift({
                                        type: "img",
                                        name: this.noImage
                                    })
                                } else {
                                    parsAllOptionItem.unshift({
                                        type: "img",
                                        name: variantValue
                                    })
                                }

                            } else {
                                parsAllOptionItem.push({
                                    input: true,
                                    name: variantValue,
                                    type: "text"
                                })
                            }
                        }
                        parsAllOptionItem.push({
                            input: true,
                            name: default_sku,
                            type: "text"
                        });
                        parsAllOptions.push(parsAllOptionItem);
                    }
                    return parsAllOptions
                },
                async requestEdit(submitData){
                    const productId = this.editData.id;
                    try{
                        this.loadingMessage = 'importProduct';
                        this.scrapUrl = '';
                        this.editData = {};
                        const response = await baseObject.fetchRequest(`${baseObject.domain}product/update?id=${productId}`, 'PUT', submitData);
                        if(response.status){
                            this.loading = false;
                            this.loadingMessage = '';
                            window.location.href = response.view;
                        }else{
                            this.loading = false;
                            this.loadingMessage = '';
                            generateError(e.message, 663);
                        }
                    }catch (e) {
                        this.loading = false;
                        $('#myModal').modal('hide');
                        generateError(e.message, 668);
                    }

                },
                openEditView(data){
                    this.displayEditAllSecCheckbox = true;
                    this.editData = data ? JSON.parse(data):{};
                    this.showData = true;
                    this.edit();
                },
                // use this version

                // start call from index.html
                changeVariantsPrice(index, trIndex, value) {
                    const variantData = this.variantsPriceMarkups.find((markup, index) => {
                        if (markup.id === this.variant_index) {
                            return markup
                        }
                    })

                    if (!variantData) {
                        if (value !== '') {
                            let byAmount = Number(this.compareAtPriceByAmount)
                        let byPercent = Number(this.compareAtPriceByPercent)
                        if (this.compareAtPriceSelected === '0') {
                            this.allOptions[index][trIndex + 1].name = (Number(value) + Number(byPercent / 100 * value)).toFixed(2);
                        } else {
                            let change = Number(value) + Number(byAmount);
                                this.allOptions[index][trIndex + 1].name = change.toFixed(2)
                            }
                        } else {
                            this.allOptions[index][trIndex + 1].name = ''
                        }
                    }

                },
                tdDisabled(val, imgOpacAndPrice = 0) {
                    if(imgOpacAndPrice === 'price'){
                        let globalPriceReadOnly = !this.tdDisabledArr.includes(val);
                        globalPriceReadOnly ? this.priceReadonly = false : this.priceReadonly = true
                    } else if(imgOpacAndPrice === 'CompareAtPrice') {
                        return !this.tdDisabledArr.includes(Number(val) - 1);
                    }else if(imgOpacAndPrice){
                        let opacity = this.tdDisabledArr.includes(val);
                        return opacity ? 1 : 0.2

                    }
                    return !this.tdDisabledArr.includes(val)
                },
                pageImageLoaded(){
                    this.pageImageLoad = 'none'
                },
                replaceLastLine(value){
                    if(value.slice(-1) === '-'){
                        return  value.replace('-', '')
                    }
                    return value
                },
                changeVariationsName(optionName,variationsValue,index,trIndex,event) {
                    const value = event.target.value;
                    const newValue = this.replaceLastLine(value);
                    let nameQueue = this.nameQueue;
                    const repeatValueIndexList = this.repeatValueIndexList;
                    const allOptions = this.allOptions;
                    let optionKeyStartIndex = this.thereIsImage ? 1 : 0;
                    let optionItemLength = this.thereIsImage ? nameQueue.length+1:nameQueue.length;
                    const fullOptionsNameArr = [...this.fullOptionsName];

                    if(optionName !== 'Quantity' &&  optionName !== 'SKU' ){
                        for (let repeatRowIndex = 0;
                             repeatRowIndex < repeatValueIndexList.length;
                             repeatRowIndex += 1) {
                            let string = '';
                            const variantRowIndex = repeatValueIndexList[repeatRowIndex];

                            for (let optionKeyIndex = optionKeyStartIndex;optionKeyIndex<optionItemLength;optionKeyIndex++) {
                                if (optionKeyIndex == trIndex) {
                                    allOptions[variantRowIndex][optionKeyIndex].name = value;
                                    string += ` ${newValue}`;
                                    continue;
                                }

                                string+=` ${this.replaceLastLine(allOptions[variantRowIndex][optionKeyIndex].name)}`
                            }

                            fullOptionsNameArr[variantRowIndex-1] = string
                        }
                        this.fullOptionsName = fullOptionsNameArr;
                    }else{
                        const oldValue = this.allOptions[index][trIndex].name;
                        const quantityValue = newValue - oldValue;
                        this.stockCount += quantityValue;
                        this.allOptions[index][trIndex].name = newValue;
                    }
                },

                changeRepeatVariantName(optionName,trIndex, index, event){
                    if( optionName !== 'SKU'){
                        const value = event.target.value;
                        let allOptions = this.allOptions;
                        let fullOptionsName = this.fullOptionsName;
                        let arrayIsUnique = new Set(fullOptionsName).size === fullOptionsName.length;
                        let repeatValueIndexList = this.repeatValueIndexList;

                        if(!arrayIsUnique || !value){
                            const currentInputValue = fullOptionsName[index-1];

                            for(let indexListIndex = 0;indexListIndex<repeatValueIndexList.length;indexListIndex++){
                                allOptions[repeatValueIndexList[indexListIndex]][trIndex].name = this.focusVariantInputValue
                            }
                            if(!value){
                                generateError(`Please fill color name`, 771, true,);

                            } else {
                                generateError(`The variant '${currentInputValue}' already exists. Please change at least one option value`, 774, true,);
                            }
                        }
                        this.fullOptionsName = [];
                        this.repeatValueIndexList = []
                    }
                },

                getFocusVariantInputValue(optionName,variationsValue,trIndex,event){
                    if( optionName !== 'Quantity' &&  optionName !== 'SKU'){
                        const value = event.target.value;
                        const newValue = this.replaceLastLine(value);

                        let nameQueue = this.nameQueue;
                        let allOptions = this.allOptions;
                        let optionKeyStartIndex = this.thereIsImage ? 1 : 0;
                        let optionItemLength = this.thereIsImage ? nameQueue.length+1:nameQueue.length;
                        const fullOptionsNameArr = [];
                        const repeatValueIndexListArr = [];

                        for (let optionItemIndex=1;optionItemIndex<allOptions.length;optionItemIndex++) {
                            let string = '';
                            for (let optionKeyIndex = optionKeyStartIndex;optionKeyIndex<optionItemLength;optionKeyIndex++) {
                                if (allOptions[optionItemIndex][optionKeyIndex].name === variationsValue &&  optionKeyIndex == trIndex) {
                                    repeatValueIndexListArr.push(optionItemIndex);
                                }

                                string+=` ${this.replaceLastLine(allOptions[optionItemIndex][optionKeyIndex].name)}`
                            }

                            fullOptionsNameArr[optionItemIndex-1] = string
                        }
                        this.repeatValueIndexList = repeatValueIndexListArr;
                        this.fullOptionsName = fullOptionsNameArr;
                        this.focusVariantInputValue = newValue;
                    }
                },

                variantImageLoaded(){
                    this.displayVariantImageLoad = 'none'
                },
                closePreferImageModal(src) {
                    $('#prefer-image-modal').modal('hide');
                    $('body').addClass('modal-open');
                    let preferSrc = this.preferVariationImageSrc.src;
                    let imgIndex = this.preferVariationImageSrc.index;
                    let imgTrIndex = this.preferVariationImageSrc.trIndex;
                    if(preferSrc.includes('no-image')){
                        this.allOptions[imgIndex][imgTrIndex].name = src;
                    }else{

                        for (let index = 0; index < this.allOptions?.length; index++) {
                            if (this.allOptions[index][0].name === preferSrc ) {
                                this.allOptions[index][0].name = src;
                            }
                        }
                    }

                },
                showAddImageModal() {
                    $('#add-image-modal').on('hidden.bs.modal', function () {
                        $('body').addClass('modal-open');
                    });
                    $('#add-image-modal').modal('show');
                },
                addImageFromModal() {
                    let patt = /(http(s?):)([/|.|\w|\s|-])*\.(?:jpg|gif|png|jpeg)/g;
                    let result = this.addImageSrc.match(patt);

                    if (result) {
                        this.displayMassageNotValidImage = 'none';
                        this.checkedImages.push(this.addImageSrc);
                        this.images.push(this.addImageSrc);
                        this.addImageSrc='';
                    } else {
                        this.displayMassageNotValidImage = 'block';
                    }
                },
                preferImage(src,index,trIndex) {
                    this.preferVariationImageSrc = {src,index,trIndex};
                    let allImageMyModal = [...this.images, ...this.onlyVariationsImage];
                    this.allImagesMyModal = allImageMyModal;
                    $('#prefer-image-modal').modal('show');
                },
                showSubscribeLinkModal(disable, siteError) {
                    if (disable) {
                        let errorType = siteError ? 'site' : 'feature';
                        let message = ` <div> 
                                                This ${errorType} is not available in your plan 
                                                <br>You can upgrade plan 
                                                <a target="_blank" href="${this.subscribeLink}">here</a>
                                            </div> `;
                        let subscribeLink = this.subscribeLink;
                        swal({
                            title: 'Plan upgrade required!',
                            html: message,
                            type: 'warning',
                            showCancelButton: !0,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Show plans",
                            cancelButtonText: "Cancel",
                        }).then(function(e) {
                            if (e.value === true) {
                                window.open(subscribeLink);
                            }
                        })
                    }
                },
                displayLightBox(imgURL){
                    this.lightBoxImgSrc = imgURL;
                    this.displayLightBoxDiv = 'block'
                },
                closeLightBox(){
                    this.displayLightBoxDiv = 'none';
                    this.lightBoxImgSrc = '';
                },
                //end call from index.html
                reviewsStarValue(starCheck){
                    if(starCheck){
                        const value = starCheck.slice(0,starCheck.lastIndexOf('%'));
                        return value / 20;
                    }
                    return 0
                },

                dateValue(value ){
                    if(value){
                        return moment(value).format('YYYY-MM-DD')
                    }
                },

                async requestAdd(submitData){
                    try {
                        this.loadingMessage = 'importProduct';
                        this.addData = {};
                        this.scrapUrl = '';
                        const response = await baseObject.create(submitData);
                        if (response.status) {
                            this.loading = false;
                            this.loadingMessage = '';
                            window.location.href = response.view;
                        } else {
                            this.loading = false;
                            this.loadingMessage = '';
                            generateError('Invalid Status', 1069);
                        }
                    }catch (e) {
                        this.loading = false;
                        $('#myModal').modal('hide');
                        generateError(e.message, 1074, true);
                    }

                },
                requestProducts(submitData) {
                    window.scrollTo(0,0);
                    this.displayEditAllSecCheckbox ? this.requestEdit(submitData) : this.requestAdd(submitData);
                },
                checkVariantsLength(submitData) {
                    if(this.chooseVariationsItems.length > 1){
                        submitData.options = this.options;
                    }
                },
                createJsonObject() {
                    return {
                        title: this.title,
                        body_html: this.description,
                        sizeTable: this.sizeTable,
                        vendor: this.vendor,
                        product_type: this.productType,
                        variants: this.checkedVariationsItems,
                        productId: this.finalSKU,
                        stockCount: this.stockCount,
                        reviews: this.checkedReviews,
                        productUrl:  this.productUrl,
                        brand: this.brand,
                        weight: this.weight,
                        weight_unit: this.selectedWeightUnit,
                        price: this.finalPrice,
                        compare_at_price: this.finalCompareAtPrice,
                        collection: this.selectedCollection,
                        new_collection_name: this.newCollectionValue,
                        images: this.checkedImages,
                        price_markup: Number(this.priceSelected),
                        compareAtPrice_markup: Number(this.compareAtPriceSelected),
                        priceByPercent: this.priceByPercent,
                        priceByAmount: this.priceByAmount,
                        compareAtPriceByAmount: this.compareAtPriceByAmount,
                        compareAtPriceByPercent: this.compareAtPriceByPercent,
                        currency_id: this.shopifyCurrency,
                        default_currency_id: this.supplierCurrency,
                        currency_rate: this.disabledImportType.product_currency_convertor ? this.currencyRate : null,
                        imported_from: this.imported_from
                    }
                },
                getCheckedReviews() {
                    let checkedReviews = [];
                    for (let reviewIndex of this.chooseReviews) {
                        checkedReviews.push(this.reviews[reviewIndex]);
                    }
                    return checkedReviews;
                },
                getCheckedVariationsItems(){
                    let checkedVariations = [];
                    let optionName = this.onlyOptionName;
                    let AllOptions = this.allOptions;
                    let ChooseVariationsItems = this.chooseVariationsItems;
                    let TdDisabledArr = this.tdDisabledArr;
                    let NameQueue = this.nameQueue;
                    if(optionName?.length){
                        for (let chooseVariantIndex = 1; chooseVariantIndex < ChooseVariationsItems.length; chooseVariantIndex++) {
                            let checkedVariationsAllProperty = {};
                            let optionIndex = 0;
                            let chooseIndex = ChooseVariationsItems[chooseVariantIndex];
                            for (let index = 0; index < AllOptions[chooseIndex].length - 1; index++) {
                                let chooseVariantValue = AllOptions[chooseIndex][index].name;

                                if (index < AllOptions[chooseIndex].length - 5) {
                                    optionIndex++;
                                    if (AllOptions[chooseIndex][index].type !== "img") {
                                        let queueIndex = NameQueue.indexOf(optionName[index]);
                                        checkedVariationsAllProperty[`option${queueIndex + 1}`] = chooseVariantValue //AllOptions[chooseIndex][index].name;

                                    } else {
                                        optionIndex--;
                                        if (TdDisabledArr.includes(index)) {
                                            checkedVariationsAllProperty.img = AllOptions[chooseIndex][index].name
                                        } else{
                                            checkedVariationsAllProperty.img = null
                                        }
                                    }
                                } else {
                                    if (!TdDisabledArr.includes(index)) {
                                        let item = this[`final${optionName[index]}`];
                                        if(item){
                                            checkedVariationsAllProperty[optionName[index] === "Quantity" ? "inventory_quantity" : optionName[index] === "CompareAtPrice" ? 'compare_at_price' : optionName[index].toLowerCase()] = item
                                        }
                                    } else {
                                        let item = AllOptions[chooseIndex][index].name;
                                        checkedVariationsAllProperty[optionName[index] === "Quantity" ? "inventory_quantity" : optionName[index] === "CompareAtPrice" ? 'compare_at_price' : optionName[index].toLowerCase()] = item
                                        if(optionName[index] === 'Price' && Number(item) < 0){
                                            this.isVariantPriceNegative = true;
                                            break;
                                        }
                                        if (optionName[index] == 'Price' && AllOptions[chooseIndex][index].changed) {
                                            checkedVariationsAllProperty['compare_at_price_by_amount'] = AllOptions[chooseIndex][index].compare_at_price_by_amount
                                            checkedVariationsAllProperty['compare_at_price_by_percent'] = AllOptions[chooseIndex][index].compare_at_price_by_percent
                                            checkedVariationsAllProperty['compare_at_price_markup'] = AllOptions[chooseIndex][index].compare_at_price_markup
                                            checkedVariationsAllProperty['price_by_amount'] = AllOptions[chooseIndex][index].price_by_amount
                                            checkedVariationsAllProperty['price_by_percent'] = AllOptions[chooseIndex][index].price_by_percent
                                            checkedVariationsAllProperty['price_markup'] = AllOptions[chooseIndex][index].price_markup
                                            checkedVariationsAllProperty['changed'] = true
                                        }
                                    }
                                }
                            }
                            checkedVariationsAllProperty['default_sku'] = AllOptions[chooseIndex][AllOptions[chooseIndex].length - 1].name;
                            checkedVariations.push(checkedVariationsAllProperty)
                        }
                    }
                    return checkedVariations
                },
                isPositivePrice() {
                    return Number(this.finalPrice) < 0 || this.isVariantPriceNegative
                },
                doSubmit({publish, imported_from}) {
                    this.imported_from = imported_from;
                    this.isVariantPriceNegative = false;
                    this.checkedReviews = this.getCheckedReviews();
                    this.checkedVariationsItems = this.getCheckedVariationsItems();
                    if(this.isPositivePrice()){
                        generateError(`Price can not be lower than 0`, 1246, true,);
                    }else if(this.checkedVariationsItems.length) {
                        let submitData = this.createJsonObject();
                        submitData.publish = publish;
                        this.checkVariantsLength(submitData);
                        this.requestProducts(submitData);
                        this.loading = true;
                    }else {
                        swal("Please select at least one variant!")
                    }
                },
                async  scrap(url,method){
                    if(method){
                        const body = JSON.stringify({url});
                        const headers = {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        };
                        const data = await fetch(`${baseObject.getReviewsUrl}`,{method,headers,body});
                        return await data.json();
                    }
                    const data = await fetch(url);
                    return await data.json();
                },
                cloneVariants(allOptions){
                    this.oldAllOptions = Array.from(JSON.parse(JSON.stringify(allOptions)));
                    this.allOptions = Array.from(JSON.parse(JSON.stringify(allOptions)));
                },
                setCompareAtPrice() {
                    this.compareAtPrice = this.finalPrice;
                },
                changeReadonlyQuantity(e) {
                    this.quantityReadonly = !e.target.checked
                },
                calculatePrice(val, mode = 0) {
                    // mode ==> 0 => byPercent; 1 => byAmount
                    //change compareAtPrice value
                    this.setCompareAtPrice();
                    let compareAtPrice = this.onlyOptionName.indexOf("CompareAtPrice");
                    let priceIndex = this.onlyOptionName.indexOf("Price");
                    let quantityIndex = this.onlyOptionName.indexOf("Quantity");
                    if(compareAtPrice !== -1 && priceIndex !== -1){
                        if (mode) {
                            for (let index = 1; index < this.allOptions.length; index++) {
                                if (!this.allOptions[index][priceIndex].changed){
                                    let price = (Number(this.oldAllOptions[index][priceIndex].name) * Number(this.currencyRate)) +val;
                                    this.allOptions[index][priceIndex].name = this.priceReadonly ? this.finalPrice : Number(price).toFixed(2);
                                    this.allOptions[index][compareAtPrice].name = this.priceReadonly ? this.finalCompareAtPrice : (Number(this.allOptions[index][priceIndex].name) + Number(this.compareAtPriceByAmount)).toFixed(2);
                                } else {
                                    const variantData = this.variantsPriceMarkups.find(markup => markup.id === index)
                                    let price = (Number(this.oldAllOptions[index][priceIndex].name) * Number(this.currencyRate)) + variantData.price_by_amount;
                                    this.allOptions[index][priceIndex].name = this.priceReadonly ? this.finalPrice : Number(price).toFixed(2);
                                    this.allOptions[index][compareAtPrice].name = this.priceReadonly ? this.finalCompareAtPrice : (Number(this.allOptions[index][priceIndex].name) + Number(this.variantData.compare_at_price_by_amount)).toFixed(2);
                                }
                                if (this.quantityReadonly) {
                                    this.allOptions[index][quantityIndex].name = this.stockCount
                                } else {
                                    this.allOptions[index][quantityIndex].name = this.oldAllOptions[index][quantityIndex].name
                                }
                            }
                        } else {
                            for (let index = 1; index < this.allOptions.length; index++) {
                                let priceIndexValue = Number(this.oldAllOptions[index][priceIndex].name) * Number(this.currencyRate)
                                if (!this.allOptions[index][priceIndex].changed) {
                                    let price = Number(priceIndexValue + (val / 100 * priceIndexValue)).toFixed(2);
                                    this.allOptions[index][priceIndex].name = this.priceReadonly ? this.finalPrice : price;
                                    this.allOptions[index][compareAtPrice].name = this.priceReadonly ? this.finalCompareAtPrice : (Number(price) + (this.compareAtPriceByPercent / 100 * price)).toFixed(2);
                                } else {
                                    const variantData = this.variantsPriceMarkups.find(markup => markup.id === index)
                                    let price = Number(priceIndexValue + (variantData.price_by_percent / 100 * priceIndexValue)).toFixed(2);
                                    this.allOptions[index][priceIndex].name = this.priceReadonly ? this.finalPrice : price;
                                    this.allOptions[index][compareAtPrice].name = this.priceReadonly ? this.finalCompareAtPrice : (Number(price) + (Number(variantData.compare_at_price_by_percent) / 100 * Number(price))).toFixed(2)
                                }

                                if (this.quantityReadonly) {
                                    this.allOptions[index][quantityIndex].name = this.stockCount
                                } else {
                                    this.allOptions[index][quantityIndex].name = this.oldAllOptions[index][quantityIndex].name
                                }
                            }
                        }
                    }
                },
                calculateCompareAtPrice(val, mode = 0) {
                    // mode ==> 0 => byPercent; 1 => byAmount
                    let compareAtPrice = this.onlyOptionName.indexOf("CompareAtPrice");
                    let priceIndex = this.onlyOptionName.indexOf("Price");
                    if(compareAtPrice !== -1 && priceIndex !== -1){
                        if (mode) {
                            for (let index = 1; index < this.allOptions.length; index++) {
                                if (!this.allOptions[index][priceIndex].changed) {
                                    let comparePrice = (Number(this.allOptions[index][priceIndex].name)  * Number(this.currencyRate)) + Number(val);
                                    this.allOptions[index][compareAtPrice].name = comparePrice.toFixed(2)
                                }
                            }
                        } else {
                            for (let index = 1; index < this.allOptions.length; index++) {
                                if (!this.allOptions[index][priceIndex].changed) {
                                    let priceIndexValue = Number(this.oldAllOptions[index][priceIndex].name) * Number(this.currencyRate)
                                let comparePrice = priceIndexValue + (val / 100 * priceIndexValue)
                                    this.allOptions[index][compareAtPrice].name = comparePrice.toFixed(2);
                                }
                            }
                        }
                    }
                },
                calculateVariantsPrice(){
                    if(this.priceSelected === '1'){
                        this.calculatePrice(this.defaultPriceByAmount, 1)
                    }else{
                        this.calculatePrice(this.defaultPriceByPercent)
                    }
                },
                getPriceFromUserPricingRules(price){
                    let data = {
                        priceByPercent: false,
                        priceByAmount: false,
                        comparedPriceByPercent: false,
                        comparedPriceByAmount: false,
                    }
                    if (this.disabledImportType.custom_pricing_rules) {
                        this.userProductPricingRules.every(item => {
                            if(Number(price) <= item.price_max && Number(price) >= item.price_min) {
                                data.priceByPercent = Number(item.price_by_percent)
                                data.priceByAmount = Number(item.price_by_amount)
                                data.comparedPriceByPercent = Number(item.compare_at_price_by_percent)
                                data.comparedPriceByAmount = Number(item.compare_at_price_by_amount)
                                data.priceSelected = item.price_markup
                                data.compareAtPriceSelected = item.compare_at_price_markup
                                this.minPriceMarkup = item.price_min
                                this.maxPriceMarkup = item.price_max
                                return false
                            }
                            return true
                        })
                    }
                    return data
                },
                makeDefaultVariants(){
                    const { price, finalSKU , defaultQuantity} = this;
                    return ([
                        [
                            {"type":"text","name":"Title","input":false},
                            {"type":"text","name":"SKU","input":false},
                            {"type":"text","name":"Price","input":false},
                            {"type":"text","name":"CompareAtPrice","input":false},
                            {"type":"text","name":"Quantity","input":false}
                        ],
                        [
                            {"type":"text","name":"Default Title","input":true},
                            {"type":"text","name":finalSKU,"input":true},
                            {"type":"text","name":price,"input":true},
                            {"type":"text","name":price,"input":true},
                            {"type":"text","name":defaultQuantity,"input":true},
                            {"type":"text","name":finalSKU,"input":true}
                        ]
                    ])
                },
                checkThereIsImage(){
                    if(this.onlyOptionName.includes('IMG')){
                        this.thereIsImage = true;
                    }
                },
                variantsAction(){
                    this.makeAllOptionsTdDisabled();
                    if(this.addData.variants?.length>1){
                        this.thereIsVariants = true;
                        this.checkThereIsImage();
                        this.cloneVariants(this.addData.variants);
                        this.calculateVariantsPrice()
                    }else{
                        this.addData.variants = this.makeDefaultVariants();
                        this.cloneVariants(this.addData.variants);
                        this.calculateVariantsPrice()
                    }
                },
                editVariantsAction(){
                    if(this.thereIsVariants){
                        this.editAllOptionsParse();
                        this.makeAllOptionsTdDisabled();
                        this.checkThereIsImage();
                    }
                },
                checkAddInfoHasImage(){
                    if(this.images?.length){
                        this.checkedImage(this.images);
                    }

                },
                attributionPrice(infoObject,formProperty=0){
                    // infoObject ==> informationIndexRequest
                    // formProperty ==> index => 1 , edit => 0
                    this.priceByPercent = this[infoObject][formProperty ? 'price_by_percent' : 'priceByPercent'];
                    this.priceByAmount = this[infoObject][formProperty ? 'price_by_amount' : 'priceByAmount'];
                    this.priceSelected = `${this[infoObject].price_markup}`;
                },
                attributionCompareAtPrice(infoObject,formProperty=0){
                    // infoObject ==> informationIndexRequest
                    // formProperty ==> index => 1 , edit => 0
                    this.compareAtPriceByPercent = this[infoObject][formProperty ? 'compare_at_price_by_percent' : 'compareAtPriceByPercent'];
                    this.compareAtPriceByAmount = this[infoObject][formProperty ? 'compare_at_price_by_amount' : 'compareAtPriceByAmount'];
                    this.compareAtPriceSelected = `${this[infoObject][formProperty ? 'compare_at_price_markup' : 'compareAtPrice_markup']}`;
                },
                defaultIndexPriceCompareAtPrice(){
                    const pricingRulesData = this.getPriceFromUserPricingRules(this.price)

                    this.defaultPriceByAmount = pricingRulesData.priceByAmount !== false ? pricingRulesData.priceByAmount : this.informationIndexRequest.price_by_amount ;
                    this.defaultPriceByPercent = pricingRulesData.priceByPercent !== false ? pricingRulesData.priceByPercent : this.informationIndexRequest.price_by_percent;
                    this.defaultCompareAtPriceByPercent = pricingRulesData.comparedPriceByPercent !== false ? pricingRulesData.comparedPriceByPercent : this.informationIndexRequest.compare_at_price_by_percent;
                    this.defaultCompareAtPriceByAmount = pricingRulesData.comparedPriceByAmount !== false ? pricingRulesData.comparedPriceByAmount : this.informationIndexRequest.compare_at_price_by_amount;
                    if (pricingRulesData.priceSelected) {
                        this.priceSelected = pricingRulesData.priceSelected.toString();
                    }
                    if (pricingRulesData.compareAtPriceSelected) {
                        this.compareAtPriceSelected = pricingRulesData.compareAtPriceSelected.toString();

                    }


                },
                defaultEditPriceCompareAtPrice(){
                    this.defaultEditCompareAtPriceByAmount = this.editData.compareAtPriceByAmount;
                    this.defaultEditCompareAtPriceByPercent = this.editData.compareAtPriceByPercent;
                    this.defaultEditPriceByAmount = this.editData.priceByAmount;
                    this.defaultEditPriceByPercent = this.editData.priceByPercent;
                },
                setDescription({body_html}){
                    const htmlDescription = $(body_html);
                    const descriptionItems = htmlDescription.find('.product-intro__description-table-item');
                    if(descriptionItems?.length){
                        const div = $('<div/>');
                        const ul = $('<ul/>');
                        div.append(ul);
                        descriptionItems.each((index) => {
                            const li = $('<li/>');
                            const key =  $(descriptionItems).eq(index).find('.key').text().trim();
                            const value =  $(descriptionItems).eq(index).find('.val').text().trim();
                            li.text(`${key} ${value}`);
                            ul.append(li);
                        });
                        this.description = div.html();
                    }else if(body_html.length){
                        if (typeof body_html === 'string') {
                            this.description = body_html;
                        } else {
                            const div = $('<div/>');
                            const ul = $('<ul/>');
                            div.append(ul);
                            body_html.map((item) => {
                                const li = $('<li/>');
                                const key = item.attr_name ;
                                const value = item.attr_value;
                                li.text(`${key}: ${value}`);
                                ul.append(li);
                            });
                            this.description = div.html();
                        }

                    }else{
                        this.description = body_html;
                    }
                },
                setAddData() {
                    console.log(this.addData, 'addData')
                    let price = this.addData.price * this.currencyRate;
                    const fixed = price < 1 ? ((1 / Number(price)).toFixed(0)).toString().length + 1 : 2
                    this.selectedWeightUnit = this.addData.weight_unit;
                    this.radioSelectAllOptions = 1;
                    this.radioSelectReviews = 1;
                    this.images = this.addData.images;
                    this.reviews = this.addData.reviews || [];
                    this.chooseReviews = this.addData.reviews?.data || [];
                    this.title = this.addData.title;
                    this.sizeTable = this.addData.sizeTable;
                    this.price = price.toFixed(fixed);
                    this.reservePrice = this.addData.price;
                    this.compareAtPrice = price.toFixed(fixed);
                    this.defaultQuantity = this.addData.stockCount;
                    this.stockCount = this.addData.variantsStockCount || this.defaultQuantity;
                    this.selectedCollection = '';
                    this.productUrl = this.addData.productUrl;
                    this.finalSKU = this.addData.productId;
                    this.vendor = this.addData.vendor;
                    this.productType = this.addData.product_type;
                    this.weight = this.addData.weight;
                    this.brand =  this.addData.brand;
                    this.images = this.addData.images;
                    this.onlyVariationsImage = this.addData.onlyImages;
                    this.nameQueue = this.addData.nameQueue;
                    this.onlyOptionName = this.addData.onlyOptionName;
                    this.options = this.addData.options;
                    this.attributionPrice('informationIndexRequest',1);
                    this.attributionCompareAtPrice('informationIndexRequest',1);
                    this.defaultIndexPriceCompareAtPrice();

                    this.checkAddInfoHasImage();
                    this.setDescription({body_html: this.addData.body_html});
                    this.addData.description = this.description;
                    this.variantsAction();
                    this.defaultEditAllSecCheckbox('add');
                    setTimeout(()=>{
                        $('[data-toggle="hover"]').popover({ trigger: "hover" })
                    },3000);
                    this.makeReviewsChecked()
                },
                checkedImage(images) {
                    this.checkedImages = [];
                    this.checkedImages = [...images]
                },
                checkedImageEditPage() {
                    this.checkedImages = [];
                    this.checkedImages = [...this.editData.images]
                },
                validShainProductUrl(url){
                    let patt = /-p-\d+/g;
                    return url.match(patt);
                },
                async getAddData(){
                    let scrapUrl = this.scrapUrl.trim();
                    const isShainUrl = scrapUrl.includes('shein');
                    const isShainProductUrl = this.validShainProductUrl(scrapUrl);

                    if(isShainUrl && !isShainProductUrl) {
                        generateError(this.invalidUrlMessage, 1429, true, scrapUrl);
                        return;
                    }
                    const getDataUrl = baseObject.getDataUrl.trim();
                    if (scrapUrl && !this.loading){
                        scrapUrl = scrapUrl.replace('-&-', '');
                        scrapUrl = encodeURIComponent(scrapUrl);
                        this.loading = true;
                        this.loadingMessage = 'getProductData';
                        this.addData={};
                        try {
                            let responseData = await this.scrap(`${getDataUrl}?url=${scrapUrl}`);
                            if (!responseData) {
                                this.loading = false;
                                this.scrapUrl = '';
                                this.loadingMessage = '';
                                generateError('Empty response', 1453, false, scrapUrl);
                                return false;
                            }
                            this.loading = false;
                            this.loadingMessage = '';
                            if (responseData.code !== 0) {
                                this.addData = responseData;
                                this.setAddData();
                                if (responseData.existingId) {
                                    swal({
                                        title: 'Product already exists!',
                                        html: `<p>That product already was imported in the system, <br>
                                                <a href="/product/${responseData.existingId}" target="_blank">Product id - ${responseData.existingId}</a></p>`,
                                        type: 'warning',
                                        showCancelButton: false,
                                        confirmButtonColor: "#DD6B55",
                                    })
                                }
                            } else {
                                if (responseData.status == 403) {
                                    this.showSubscribeLinkModal(true, true);
                                } else {
                                    swal('Invalid site url!');
                                }
                            }


                        } catch (e) {
                            this.loading = false;
                            this.scrapUrl = '';
                            this.loadingMessage = '';
                            generateError(e.message, 1446, true)
                        }
                    }
                },
                openRequestSiteModal()
                {
                    $('#requestSiteModal').modal('show');
                },
                openHelpModal(key){
                    this.helpMessageKey = key;
                    $('#helpModal').modal('show');
                },
                isVariantPriceMarkupChanged(id) {
                    return this.variantsPriceMarkups.find(markup => markup.id === id) ? 'text-primary' : false;
                },
                openVariantPriceMarkupModal(id){
                    if (this.disabledImportType.variant_price_markup) {
                        const variantData = this.variantsPriceMarkups.find(markup => markup.id === id)
                        const priceIndex = this.onlyOptionName.indexOf("Price")
                        const comparePriceIndex = this.onlyOptionName.indexOf('CompareAtPrice')
                        this.variant_price = variantData ? variantData.price :(Number(this.oldAllOptions[id][priceIndex].name) * Number(this.currencyRate)).toFixed(2)
                        this.variant_final_price = variantData ? variantData.variant_final_price : (Number(this.allOptions[id][priceIndex].name)).toFixed(2)
                        this.variant_compare_at_price = variantData ? variantData.variant_compare_at_price : (Number(this.allOptions[id][comparePriceIndex].name)).toFixed(2)
                        this.variant_price_markup = variantData ? variantData.price_markup : this.priceSelected
                        this.variant_compare_at_price_markup = variantData ? variantData.compare_at_price_markup : this.compareAtPriceSelected
                        this.variant_price_by_percent = variantData ? variantData.price_by_percent : this.priceByPercent
                        this.variant_price_by_amount = variantData ? variantData.price_by_amount : this.priceByAmount
                        this.variant_compare_at_price_by_amount = variantData ? variantData.compare_at_price_by_amount: this.compareAtPriceByAmount
                        this.variant_compare_at_price_by_percent = variantData ? variantData.compare_at_price_by_percent :  this.compareAtPriceByPercent
                        this.variant_index = id
                        this.variant_get_from_product_markup = !variantData
                        $('#variantPriceMarkupModal').modal('show');
                    } else {
                        this.showSubscribeLinkModal(true, false)
                    }

                },
                changeSelectedCollection(event) {
                    if(event.target.value === 'create_new'){
                        this.showCreateCollectionInput = true;
                        this.selectedCollection = '';
                        return;
                    }
                    this.showCreateCollectionInput = false;
                    this.newCollectionValue = "";
                },
            }
        });
        await this.setIndexData(data, baseObject);
        await this.setCurrenciesData(data, baseObject)
        if(data.editData && data.editData?.length){
            this.vueInstance.openEditView(data.editData)
        }
    }

    ////////////////////// new added

    fetchRequest(url,method,body){
        let requestParams = {
            method,
            withCredentials: true,
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Auth': `Bearer ${this.accessToken}`
            }
        };
        if(body){
            requestParams.body = JSON.stringify(body)
        }
        return (
            fetch(url,requestParams)
                .then((response) => (
                        response.json()
                            .then(json => (
                                response.ok ? json : Promise.reject(json)
                            ))
                    )
                )
        )
    }


    async create(requestData) {
        const data = await this.fetchRequest(`${this.domain}product/create`, 'POST', requestData);
        return data;
    }


}
new Popup();



function generateError(data , line, displayMessage, url) {
    swal({
        title: 'Error',
        html: displayMessage ? data : 'Something went wrong',
        type: 'error',
        showCancelButton: 0,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Ok",
    }).then(function(e) {
        $.ajax({
            method: "POST",
            url: "/product/pop-up-errors",
            data: {
                'line': line,
                'message' : data,
                'url': url
            },
        });
    })
}
