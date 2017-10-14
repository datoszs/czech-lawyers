import React from 'react';
import PropTypes from 'prop-types';
import {Button} from 'react-bootstrap';
import styles from './SimpleFormLayout.less';

const SimpleFormLayout = ({bsStyle, submit, children}) => (
    <div className={styles.main}>
        {children}
        <Button type="submit" bsStyle={bsStyle}>{submit}</Button>
    </div>
);

SimpleFormLayout.propTypes = {
    bsStyle: PropTypes.string,
    submit: PropTypes.node.isRequired,
    children: PropTypes.node.isRequired,
};

SimpleFormLayout.defaultProps = {
    bsStyle: null,
};

export default SimpleFormLayout;
