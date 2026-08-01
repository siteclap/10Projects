/**
 * EMI Calculator block — Editor view.
 *
 * @package TenProjects
 * @since 1.0.0
 */
( function () {
    var el                = wp.element.createElement;
    var useBlockProps     = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody         = wp.components.PanelBody;
    var TextControl       = wp.components.TextControl;
    var registerBlockType = wp.blocks.registerBlockType;

    /**
     * Format number in Indian currency style for editor preview.
     */
    function formatIndianPrice( amount ) {
        if ( amount >= 10000000 ) {
            return '\u20B9' + ( amount / 10000000 ).toFixed( 2 ).replace( /\.00$/, '' ) + ' Cr';
        }
        if ( amount >= 100000 ) {
            return '\u20B9' + ( amount / 100000 ).toFixed( 2 ).replace( /\.00$/, '' ) + ' L';
        }
        return '\u20B9' + amount.toLocaleString( 'en-IN' );
    }

    registerBlockType( 'tenprojects/emi-calculator', {
        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;
            var amount        = attributes.default_amount || 7500000;
            var rate          = attributes.default_rate || 8.5;
            var tenure        = attributes.default_tenure || 20;

            var blockProps = useBlockProps( {
                style: {
                    padding: '24px',
                    backgroundColor: '#ffffff',
                    border: '1px solid #e2e8f0',
                    borderRadius: '12px',
                },
            } );

            // Calculate preview EMI.
            var loanAmount  = amount * 0.8;
            var monthlyRate = ( rate / 100 ) / 12;
            var totalMonths = tenure * 12;
            var emi;

            if ( monthlyRate > 0 ) {
                emi = loanAmount * monthlyRate * Math.pow( 1 + monthlyRate, totalMonths ) / ( Math.pow( 1 + monthlyRate, totalMonths ) - 1 );
            } else {
                emi = loanAmount / totalMonths;
            }

            return el( 'div', blockProps,
                // Inspector sidebar controls.
                el( InspectorControls, null,
                    el( PanelBody, { title: 'Default Values', initialOpen: true },
                        el( TextControl, {
                            label: 'Default Loan Amount (\u20B9)',
                            type: 'number',
                            value: String( amount ),
                            onChange: function ( val ) {
                                setAttributes( { default_amount: parseInt( val, 10 ) || 7500000 } );
                            },
                        } ),
                        el( TextControl, {
                            label: 'Default Interest Rate (%)',
                            type: 'number',
                            value: String( rate ),
                            onChange: function ( val ) {
                                setAttributes( { default_rate: parseFloat( val ) || 8.5 } );
                            },
                        } ),
                        el( TextControl, {
                            label: 'Default Tenure (years)',
                            type: 'number',
                            value: String( tenure ),
                            onChange: function ( val ) {
                                setAttributes( { default_tenure: parseInt( val, 10 ) || 20 } );
                            },
                        } )
                    )
                ),

                // Block preview.
                el( 'div', null,
                    el( 'h3', {
                        style: {
                            margin: '0 0 16px',
                            fontSize: '18px',
                            fontWeight: '600',
                            color: '#1e293b',
                        },
                    }, 'EMI Calculator' ),

                    el( 'div', {
                        style: {
                            display: 'flex',
                            justifyContent: 'space-between',
                            fontSize: '13px',
                            color: '#64748b',
                            marginBottom: '8px',
                        },
                    },
                        el( 'span', null, 'Amount: ' + formatIndianPrice( amount ) ),
                        el( 'span', null, 'Rate: ' + rate + '%' ),
                        el( 'span', null, 'Tenure: ' + tenure + ' yrs' )
                    ),

                    el( 'div', {
                        style: {
                            padding: '16px',
                            backgroundColor: '#f0fdf4',
                            borderRadius: '8px',
                            textAlign: 'center',
                        },
                    },
                        el( 'div', {
                            style: {
                                fontSize: '24px',
                                fontWeight: '700',
                                color: '#16a34a',
                            },
                        }, '\u20B9' + Math.round( emi ).toLocaleString( 'en-IN' ) + '/mo' ),
                        el( 'div', {
                            style: {
                                fontSize: '12px',
                                color: '#64748b',
                                marginTop: '4px',
                            },
                        }, 'Based on 80% loan-to-value ratio' )
                    )
                )
            );
        },
    } );
} )();
