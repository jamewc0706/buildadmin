import createAxios from '/@/utils/axios'
import { demandRecord } from '/@/api/controllerUrls'

export function getSelect() {
    return createAxios({
        url: demandRecord + 'getSelect',
        method: 'get',
    })
}
