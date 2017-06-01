import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {toObject} from '../util';
import submodules from './submodules';
import {getType} from './selectors';

const containerMap = submodules
    .map((submodule) => [submodule.NAME, submodule.Container])
    .reduce(toObject, {});

const Container = ({type}) => {
    const Component = containerMap[type];
    if (Component) {
        return <Component />;
    } else {
        return null;
    }
};

Container.propTypes = {
    type: PropTypes.string,
};

Container.defaultProps = {
    type: null,
};

const mapStateToProps = (state) => ({
    type: getType(state),
});

export default connect(mapStateToProps)(Container);
