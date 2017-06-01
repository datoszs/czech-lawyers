import React from 'react';
import PropTypes from 'prop-types';

const If = ({test, Component, ...rest}) => test && <Component {...rest} />;

If.propTypes = {
    test: PropTypes.bool.isRequired,
    Component: PropTypes.func.isRequired,
};

export default If;
