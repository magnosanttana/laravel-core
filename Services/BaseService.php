<?php

namespace Modules\Core\Services;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
//use App\Exceptions\RepositoryMethodNotFoundException;
use App\Exceptions\ValidatorException;

abstract class BaseService
{
    protected $model;
    protected $modelName = '';
    protected $isModel = true;

    public function __construct()
    {
        if($this->isModel){
            if(!$this->modelName)
            {
                $classExp = explode('\\', get_class($this));
                $className = array_pop($classExp);
                $modelName = str_replace('Service', '', $className);
                $this->model = app('Modules\\'.$classExp[1].'\Models\\'. $modelName);
            }
            else
            {
                $this->model = app($this->modelName);
            }
        }


    }
    /**
     * Encontra Todos
     *
     * @return mixed
     */
    final public function lists()
    {
        return $this->model->all();
    }


    /**
     * Encontra utilizando o id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->model->find($id);
    }


    /**
     * Método que insere um novo registro.
     *
     * @param array $preData
     *
     * @return mixed
     */
    final public function create(array $preData)
    {
        $entity = new \stdClass();

        $data = $this->creating($preData);
        try
        {
            $entity = $this->model->create($data ?? $preData);
        }
        catch (\Exception $e)
        {
            throw $e;
        }

        $this->created($entity, $preData);

        return $entity;
    }

    /**
     * Método que atualiza um registro.
     *
     * @param int   $id
     * @param array $preData
     *
     * @return mixed
     */
    final public function update(int $id, array $preData)
    {

        $entity = new \stdClass();
        $data = $this->updating($id, $preData);
        #dd($id, $data, $preData);
        try
        {
            #dd( $this->model );
            $entity = $this->model->find($id);
            $entity->update($data ?? $preData);
        }
        catch (\Exception $e)
        {
            throw $e;
        }


        $this->updated($entity, $preData);
        return $entity;
    }

    /**
     * Método que exclui um registro.
     *
     * @author Magno Santana <magno@transoft.com.br>
     * @param int $id
     */

    final public function delete(int $id)
    {
        $this->deleting($id);
        $entity = $this->find($id);
        try
        {
            $entity->delete($id);
        }
        catch (\Exception $e)
        {
            throw $e;

        }

        $this->deleted($entity);
        return $entity;
    }


    /**
     * Método chamado antes da inserção ao banco.
     * @param array $data
     *
     * @return array
     * @implements
     */
    protected function creating (array $data){
        return $data;
    }

    /**
     * Método chamado antes da atualização ao banco.
     * @param int   $id
     * @param array $data
     *
     * @return array
     */
    protected function updating (int $id, array $data){
        return $data;
    }

    /**
     * Método chamado antes da deleção ao banco.
     * @param int $id
     *
     * @return void
     */
    protected function deleting (int $id){ }

    /**
     * Método chamado após a inserção ao banco.
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array $originalData
     *
     * @return void
     */
    protected function created (\Illuminate\Database\Eloquent\Model $entity, array $originalData){}

    /**
     * Método chamado após a atualização ao banco.
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param array $originalData
     *
     * @return void
     */
    protected function updated (\Illuminate\Database\Eloquent\Model $entity, array $originalData){}

    /**
     * Método chamado após a deleção ao banco.
     * @param \Illuminate\Database\Eloquent\Model $entity
     *
     * @return void
     */
    protected function  deleted ($entity){}

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @throws ValidatorException
     */
    final protected function validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($data, $rules, $messages, $customAttributes);

        if($validator->fails())
        {
            $this->throwValidationException($validator);
        }
    }

    /**
     * Remove dados no array que não devem ir.
     *
     * @param array $data
     * @param array $excepts
     * @return array
     */
    final protected function except(array $data, array $excepts)
    {
        foreach($excepts as $except)
        {
            if(isset($data[$except]))
            {
                unset($data[$except]);
            }
        }

        return $data;
    }

    /**
     * Extrai os campos do array
     *
     * @param array $data
     * @param array $extracts
     * @param bool $required
     *
     * @return array
     *
     * @throws \Exception
     */
    final protected function extract(array $data, array $extracts, $required = false)
    {
        $dataExtracts = [];
        foreach($extracts as $extract)
        {
            if(isset($data[$extract]))
            {
                $dataExtracts[$extract] = $data[$extract];
            }
            else
            {
                if($required)
                {
                    throw new \Exception(self::ERROR_1000);
                }
            }
        }

        return $dataExtracts;
    }

    /**
     * Insere um array na $data
     *
     * @param array $data
     * @param array $inserts
     *
     * @return array
     */
    final protected function insert(array $data, array $inserts)
    {
        return array_merge($data, $inserts);
    }

    /**
     * Pega a instancia de validação do Laravel.
     *
     * @return ValidationFactory
     */
    private function getValidationFactory()
    {
        return app(ValidationFactory::class);
    }

    /**
     * @param Validator $validator
     * @throws ValidatorException
     */
    private function throwValidationException(Validator $validator)
    {
        //$validator->getMessageBag()
        throw new ValidatorException($validator->errors()->all());
    }

    protected function convertDataBrToSql($dataBr){
        $data = explode('/',$dataBr);
        $dataSql = $data[2].'-'.$data[1].'-'.$data[0];


        return $dataSql;
    }
}
