/*
 * Block editor definition for the contact form block.
 *
 * Package: SimpleContactBlock
 */

const { registerBlockType } = wp.blocks;
const { useBlockProps, InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, ToggleControl } = wp.components;
const { Fragment, createElement } = wp.element;

registerBlockType('scb/contact-form', {
    title: 'Contact Form',
    icon: 'email',
    category: 'widgets',

    attributes: {
        buttonText: {
            type: 'string',
            default: 'Send Message'
        },
        showSubject: {
            type: 'boolean',
            default: true
        }
    },

    edit: (props) => {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps({ className: 'scb-block' });

        return createElement(Fragment, null, [
            createElement(
                InspectorControls,
                {},
                createElement(
                    PanelBody,
                    { title: 'Form Settings', initialOpen: true },
                    createElement(TextControl, {
                        label: 'Button Text',
                        value: attributes.buttonText,
                        onChange: (val) => setAttributes({ buttonText: val })
                    }),
                    createElement(ToggleControl, {
                        label: 'Show Subject Field',
                        checked: attributes.showSubject,
                        onChange: (val) => setAttributes({ showSubject: val })
                    })
                )
            ),
            createElement('div', blockProps, [
                createElement('form', { className: 'scb-form scb-form--preview' }, [
                    createElement('div', { className: 'scb-field' }, [
                        createElement('label', { className: 'scb-label' }, 'Name'),
                        createElement('input', { className: 'scb-input', type: 'text', disabled: true })
                    ]),
                    createElement('div', { className: 'scb-field' }, [
                        createElement('label', { className: 'scb-label' }, 'Email'),
                        createElement('input', { className: 'scb-input', type: 'email', disabled: true })
                    ]),
                    attributes.showSubject ? createElement('div', { className: 'scb-field' }, [
                        createElement('label', { className: 'scb-label' }, 'Subject'),
                        createElement('input', { className: 'scb-input', type: 'text', disabled: true })
                    ]) : null,
                    createElement('div', { className: 'scb-field' }, [
                        createElement('label', { className: 'scb-label' }, 'Message'),
                        createElement('textarea', { className: 'scb-textarea', rows: 4, disabled: true })
                    ]),
                    createElement('div', { className: 'scb-actions' }, [
                        createElement('button', { className: 'scb-button', type: 'button', disabled: true }, attributes.buttonText)
                    ])
                ])
            ])
        ]);
    },

    save: () => {
        return null;
    }
});
