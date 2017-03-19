import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Button} from 'react-bootstrap';
import {Msg} from '../containers';
import {getAdvocate} from './selectors';

const CakLinkComponent = ({href}) => (
    <Button href={href} disabled={!href} target="_blank"><Msg msg="cak.link" /></Button>
);

CakLinkComponent.propTypes = {
    href: PropTypes.string,
};

CakLinkComponent.defaultProps = {
    href: null,
};

const mapStateToProps = (state) => {
    const advocate = getAdvocate(state);
    return {href: advocate ? advocate.remoteUrl : null};
};

export default connect(mapStateToProps)(CakLinkComponent);