import Accordion from "../../libs/accordion/Accordion";

export default class MonthlyPayments {

    public init()
    {
        this.addMonthlyPaymentSummaryToAccordionHeader();
    }

    /**
     * TODO: refractor and make it reusable + make additional func. for payments
     */
    addMonthlyPaymentSummaryToAccordionHeader() {
        let accordionWrapper  = $(Accordion.selectors.ids.accordionId);
        let accordionSections = $(accordionWrapper).find(Accordion.selectors.classes.accordionSectionClass);

        $(accordionSections).each((index, element) => {
            let header         = $(element).find('h3');
            let paymentSummary = $(element).find('section.monthly-summary .amount').html();
            $(header).find('.payment-summary').html(' ( ' + paymentSummary + ' )');
        });
    }
}