import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {Row} from 'react-bootstrap';
import translate from '../translate';
import {transition} from '../util';
import {Statistics, DetailPanel} from '../components';
import {Advocate, statusMsg} from '../model';
import advocateModule from '../advocate';
import {getAdvocate} from './selectors';
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
    const advocate = getAdvocate(state, id);
    return {
        advocate,
        msgStatus: translate.getMessage(state, statusMsg[advocate.status]),
        msgIc: translate.getMessage(state, 'advocate.ic'),
    };
};

const mapDispatchToProps = () => ({
    handleDetail: (id) => () => transition(advocateModule, {id}),
});

const mergeProps = (stateProps, {handleDetail}, {id}) => ({
    handleDetail: handleDetail(id),
    ...stateProps,
});

const AdvocateDetail = connect(mapStateToProps, mapDispatchToProps, mergeProps)(AdvocateDetailComponent);

AdvocateDetail.propTypes = {
    id: PropTypes.number.isRequired,
};

export default AdvocateDetail;
