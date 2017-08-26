import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Msg} from '../containers';
import {StatementContainer, Statement} from '../components/statement';
import router from '../router';
import {STATEMENTS, STATEMENTS_ADVOCATES, STATEMENTS_CASES, STATEMENTS_PROCEEDINGS} from '../routes';

const params = {
    ending: ' ...',
};

const Container = ({transition}) => (
    <StatementContainer>
        <Statement onClick={transition(STATEMENTS_CASES)}><Msg msg="statement.cases" params={params} /></Statement>
        <Statement onClick={transition(STATEMENTS_PROCEEDINGS)}><Msg msg="statement.proceedings" params={params} /></Statement>
        <Statement onClick={transition(STATEMENTS_ADVOCATES)}><Msg msg="statement.advocates" params={params} /></Statement>
    </StatementContainer>
);

Container.propTypes = {
    transition: PropTypes.func.isRequired,
};

const mapDispatchToProps = (dispatch) => ({
    transition: (anchor) => () => dispatch(router.transition(STATEMENTS, undefined, undefined, anchor)),
});

export default connect(undefined, mapDispatchToProps)(Container);
