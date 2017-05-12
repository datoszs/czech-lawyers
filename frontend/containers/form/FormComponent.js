import React from 'react';
import {connect} from 'react-redux';
import translate from '../../translate';

const filter = (props, keys) => Object.entries(props)
    .filter(([key]) => keys.includes(key))
    .reduce((result, [key, value]) => Object.assign({[key]: value}, result), {});

const mapStateToProps = (state, {meta}) => ({
    error: (meta.invalid && meta.touched) ? translate.getMessage(state, meta.error) : null,
});

export default (LayoutComponent) => (InputComponent) => {
    const layoutPropKeys = Object.keys(LayoutComponent.propTypes);
    const inputPropKeys = Object.keys(InputComponent.propTypes);
    const FormComponent = (props) => {
        const layoutProps = filter(props, layoutPropKeys);
        const inputProps = filter(props, inputPropKeys);
        return <LayoutComponent {...layoutProps}><InputComponent {...inputProps} /></LayoutComponent>;
    };
    return connect(mapStateToProps)(FormComponent);
};
