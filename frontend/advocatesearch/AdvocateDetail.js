import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Row} from 'react-bootstrap';
import translate from '../translate';
import router from '../router';
import {ADVOCATE_DETAIL} from '../routes';
import {Statistics} from '../components/statistics';
import {DetailPanel} from '../components';
import {Advocate, statusMsg} from '../model';
import {search} from './modules';
import FooterColumn from './FooterColumn';
import Legend from './Legend';

const AdvocateDetailComponent = ({advocate, handleDetail, msgStatus, msgIc}) => (
    <DetailPanel
        footer={
            <Row>
                <FooterColumn value={advocate.address.city} />
                <FooterColumn value={advocate.ic} label={msgIc} />
                <FooterColumn value={msgStatus} />
            </Row>
        }
        title={advocate.name}
        onClick={handleDetail}
    >
        <Statistics
            positive={advocate.statistics.positive}
            negative={advocate.statistics.negative}
            neutral={advocate.statistics.neutral}
            legend={Legend}
        />
    </DetailPanel>
);

AdvocateDetailComponent.propTypes = {
    advocate: PropTypes.instanceOf(Advocate).isRequired,
    handleDetail: PropTypes.func.isRequired,
    msgStatus: PropTypes.string.isRequired,
    msgIc: PropTypes.string.isRequired,
};

const mapStateToProps = (state, {id}) => {
    const advocate = search.getResult(state, id);
    return {
        advocate,
        msgStatus: translate.getMessage(state, statusMsg[advocate.status]),
        msgIc: translate.getMessage(state, 'advocate.ic'),
    };
};

const mapDispatchToProps = (dispatch, {id}) => ({
    handleDetail: () => dispatch(router.transition(ADVOCATE_DETAIL, {id})),
});

const AdvocateDetail = connect(mapStateToProps, mapDispatchToProps)(AdvocateDetailComponent);

AdvocateDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default AdvocateDetail;
